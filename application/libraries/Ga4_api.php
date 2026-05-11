<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ga4_api
{
    const TOKEN_ENDPOINT = 'https://oauth2.googleapis.com/token';
    const REPORT_URL = 'https://analyticsdata.googleapis.com/v1beta/properties/';
    const SCOPE = 'https://www.googleapis.com/auth/analytics.readonly';
    const METRIC_KEYS = [
        'screenPageViews',
        'activeUsers',
        'averageSessionDuration',
        'bounceRate',
        'sessions',
    ];

    private $property_id;
    private $service_account;
    private $access_token = null;
    private $token_expires = 0;
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->library('request');
        $this->CI->load->model('Platform_credential_model');

        // load credential from database
        $cred = $this->CI->Platform_credential_model->get_by_platform('ga4');
        $this->property_id = $cred['property_id'];
        $this->service_account = $cred['service_account'];

        if (empty($this->service_account['private_key'])) {
            throw new \RuntimeException('GA4 credential tidak lengkap: private_key tidak ditemukan.');
        }
    }

    private function get_access_token()
    {
        // check if access token is exists and not expired
        if ($this->access_token && time() < ($this->token_expires - 60)) {
            return $this->access_token;
        }

        // generate new access token
        $jwt = $this->make_jwt();
        $body = [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $jwt,
        ];
        $response = $this->CI->request->post_form(self::TOKEN_ENDPOINT, [], $body);

        $this->access_token = $response['access_token'];
        $this->token_expires = time() + (int) ($response['expires_in'] ?? 3600);

        return $this->access_token;
    }

    private function make_jwt()
    {
        $header = $this->base64url_encode(json_encode([
            'alg' => 'RS256',
            'typ' => 'JWT',
        ]));

        $now = time();
        $payload = $this->base64url_encode(json_encode([
            'iss' => $this->service_account['client_email'],
            'scope' => self::SCOPE,
            'aud' => self::TOKEN_ENDPOINT,
            'iat' => $now,
            'exp' => $now + 3600,
        ]));

        // sign jwt
        $data_to_sign = $header . '.' . $payload;
        $private_key = openssl_pkey_get_private($this->service_account['private_key']);
        openssl_sign($data_to_sign, $signature, $private_key, 'SHA256');

        return $data_to_sign . '.' . $this->base64url_encode($signature);
    }

    private function base64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    // $since : (YYYY-MM-DD / '30daysAgo')
    // $until : (YYYY-MM-DD / 'today')
    // $hostnames : ['a.com','b.com']
    // $html_filter : ['<a class="tag" href="https://www.web.co.id/economics">economics</a>']
    public function get_articles($since, $until, $hostnames = [], $html_filter = [])
    {
        // set report body
        $body = [
            'dateRanges' => [[
                'startDate' => $since,
                'endDate' => $until,
            ]],
            'dimensions' => [
                ['name' => 'hostName'],
                ['name' => 'pagePath'],
                ['name' => 'pageTitle'],
            ],
            'metrics' => array_map(fn($k) => ['name' => $k], self::METRIC_KEYS),
            'limit' => 10000,
        ];

        // set url
        $url = self::REPORT_URL . $this->property_id . ':runReport';

        // set access token
        $token = $this->get_access_token();

        // get report from ga4 api
        $report = $this->CI->request->report($url, $body, $token);
        $rows = $report['rows'] ?? [];

        // if no filter applied, return all
        if (empty($hostnames) && empty($html_filter)) {
            return $this->split($rows);
        }

        // initialize filtered rows
        $filtered_rows = [];
        foreach ($rows as $row) {
            $hostname = $row['dimensionValues'][0]['value'] ?? '';
            $page_path = $row['dimensionValues'][1]['value'] ?? '';

            // filter 1 hostname match
            if (!empty($hostnames)) {
                if (in_array(strtolower($hostname), $hostnames, TRUE)) {
                    $filtered_rows[] = $row;
                    continue;
                }
            }

            // filter 2 html element
            if (!empty($html_filter)) {
                $full_url = 'https://' . $hostname . $page_path;

                // scrape html from url
                $html = $this->CI->request->scrape($full_url);

                // filter html element
                if ($html !== NULL && $this->html_has_element($html, $html_filter)) {
                    $filtered_rows[] = $row;
                }
            }
        }

        return $this->split($filtered_rows);
    }

    // $since : (YYYY-MM-DD / '30daysAgo')
    // $until : (YYYY-MM-DD / 'today')
    // $urls : ['m.detik.com/inet/science/d-5471469/kenapa-kucing-suka-tidur-di-atas-keyboard-laptop']
    public function sync_articles_insight($since, $until, $urls = [])
    {
        // if no urls, return empty array
        if (empty($urls)) {
            return [];
        }
        
        // extract page path from urls
        $page_paths = [];
        foreach ($urls as $url) {
            $fullUrl = 'https://' . $url;
            $path = parse_url($fullUrl, PHP_URL_PATH);
            $page_paths[] = $path;
        }

        // set report body
        $body = [
            'dateRanges' => [[
                'startDate' => $since,
                'endDate' => $until,
            ]],
            'dimensions' => [
                ['name' => 'hostName'],
                ['name' => 'pagePath'],
                ['name' => 'pageTitle'],
            ],
            'metrics' => array_map(fn($k) => ['name' => $k], self::METRIC_KEYS),
            'dimensionFilter' => [
                'filter' => [
                    'fieldName' => 'pagePath',
                    'inListFilter' => [
                        'values' => $page_paths,
                        'caseSensitive' => FALSE,
                    ],
                ],
            ],
            'limit' => 10000,
        ];

        // set url
        $url = self::REPORT_URL . $this->property_id . ':runReport';

        // set access token
        $token = $this->get_access_token();

        // get report from ga4 api
        $report = $this->CI->request->report($url, $body, $token);
        $rows = $report['rows'] ?? [];

        return $this->split($rows);
    }

    // check if $html contains any of string in $filter
    private function html_has_element(string $html, array $filter): bool
    {
        if (empty($filter)) {
            return FALSE;
        }

        foreach ($filter as $html_snippet) {
            // normalize html to make it easier to match
            $normalized_html = str_replace("='", '="', str_replace("'", '"', $html));
            $normalized_snippet = str_replace("='", '="', str_replace("'", '"', $html_snippet));

            // check if html contains snippet
            if (stripos($normalized_html, $normalized_snippet) !== FALSE) {
                return TRUE;
            }
        }

        return FALSE;
    }

    private function split($rows)
    {
        $ad_contents = [];
        $ad_metrics  = [];

        foreach ($rows as $row) {
            $hostname   = $row['dimensionValues'][0]['value'] ?? '';
            $page_path  = $row['dimensionValues'][1]['value'] ?? '';
            $page_title = $row['dimensionValues'][2]['value'] ?? '';

            if (empty($page_path) || $page_path === '(not set)' || $page_path === '/' || $page_path === '') continue;

            $full_url = $hostname . $page_path;

            $ad_contents[] = [
                'page_path'  => $full_url,
                'page_title' => $page_title,
            ];

            $ad_metrics[] = [
                'page_path' => $full_url,
                'metrics'   => $this->normalize_metrics($row['metricValues']),
            ];
        }

        return [
            'ad_contents' => $ad_contents,
            'ad_metrics'  => $ad_metrics,
        ];
    }

    private function normalize_metrics($metric_values)
    {
        $result = [];
        foreach (self::METRIC_KEYS as $i => $key) {
            $result[$key] = (float) ($metric_values[$i]['value'] ?? 0);
        }
        return $result;
    }
}