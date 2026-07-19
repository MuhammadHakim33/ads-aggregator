<?php
require_once APPPATH . 'core/Platform_driver.php';

class Ga4Driver extends Platform_driver
{
    protected $url_token = 'https://oauth2.googleapis.com/token';
    protected $url_report = 'https://analyticsdata.googleapis.com/v1beta/properties/';
    protected $scope = 'https://www.googleapis.com/auth/analytics.readonly';
    protected $metric_keys = [];

    protected $property_id;
    protected $service_account;
    protected $access_token = null;
    protected $token_expires = 0;

    public function __construct()
    {
        parent::__construct();
        $this->CI->config->load('platforms');

        $credentials = $this->CI->Platform_credential_model->get_by_platform('ga4');
        if (empty($credentials) || empty($credentials['property_id']) || empty($credentials['service_account']['private_key'])) {
            throw new \Exception("credentials for ga4 are empty or incomplete.");
        }

        $this->property_id = $credentials['property_id'] ?? '';
        $this->service_account = $credentials['service_account'] ?? '';

        $platform_config = $this->CI->config->item('platforms')['ga4'] ?? [];
        $this->metric_keys = $platform_config['metrics'] ?? [];
    }

    public function name()
    {
        return 'ga4';
    }

    private function get_access_token()
    {
        // use the same token if it is not expired yet
        if ($this->access_token && time() < ($this->token_expires - 60)) {
            return $this->access_token;
        }

        $jwt = $this->make_jwt();
        $body = [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ];

        // send request to get access token
        $response = $this->CI->request->post_form($this->url_token, [], $body);

        $this->access_token = $response['access_token'];
        $this->token_expires = time() + (int) ($response['expires_in'] ?? 3600);

        return $this->access_token;
    }

    private function make_jwt()
    {
        $header = $this->base64url_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $now = time();
        $payload = $this->base64url_encode(json_encode([
            'iss' => $this->service_account['client_email'],
            'scope' => $this->scope,
            'aud' => $this->url_token,
            'iat' => $now,
            'exp' => $now + 3600,
        ]));

        $data_to_sign = $header . '.' . $payload;
        $private_key = openssl_pkey_get_private($this->service_account['private_key']);
        openssl_sign($data_to_sign, $signature, $private_key, 'SHA256');

        return $data_to_sign . '.' . $this->base64url_encode($signature);
    }

    private function base64url_encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public function fetch_contents($since, $until, $filters = [])
    {
        $today = date('Y-m-d');
        if ($until > $today) $until = $today;
        if ($since > $today) $since = $today;

        $hostnames = $filters['hostnames'] ?? [];
        $html_filter = $filters['html'] ?? [];

        $body = [
            'dateRanges' => [['startDate' => $since, 'endDate' => $until]],
            'dimensions' => [
                ['name' => 'hostName'],
                ['name' => 'pagePath'],
                ['name' => 'pageTitle'],
            ],
            'metrics' => [
                ['name' => 'eventCount']
            ],
            'limit' => 10000,
        ];

        // send request to get report
        $url = $this->url_report . $this->property_id . ':runReport';
        $token = $this->get_access_token();
        $report = $this->CI->request->report($url, $body, $token);
        $rows = $report['rows'] ?? [];

        // filter rows based on hostname match or html element scraping
        $filtered_rows = [];
        if (empty($hostnames) && empty($html_filter)) {
            $filtered_rows = $rows;
        } else {
            // normalize hostnames to lowercase
            $hostnames = array_map('strtolower', $hostnames);

            foreach ($rows as $row) {
                $hostname = strtolower($row['dimensionValues'][0]['value'] ?? '');
                $page_path = $row['dimensionValues'][1]['value'] ?? '';

                // filter 1: hostname match
                if (!empty($hostnames) && in_array($hostname, $hostnames, true)) {
                    $filtered_rows[] = $row;
                    continue;
                }

                // filter 2: html element scraping
                if (!empty($html_filter)) {
                    $full_url = 'https://' . $hostname . $page_path;
                    $html = $this->CI->request->scrape($full_url);

                    if ($html !== null && $this->html_has_element($html, $html_filter)) {
                        $filtered_rows[] = $row;
                    }
                }
            }
        }

        $result = $this->split_report_data($filtered_rows);
        $raw = $result['ad_contents'] ?? [];

        // map to ad_contents database schema
        return array_map(function ($article) {
            return [
                'title' => mb_substr($article['page_title'] ?? '', 0, 200),
                'content_identifier' => $article['page_path'],
                'source' => isset($article['page_path']) ? 'https://' . ltrim($article['page_path'], '/') : null,
                'platform' => 'ga4',
            ];
        }, $raw);
    }

    public function fetch_insights($identifiers, $since = null, $until = null): array
    {
        $since = $since ?? date('Y-m-d', strtotime('-30 days'));
        $until = $until ?? date('Y-m-d');

        $today = date('Y-m-d');
        if ($until > $today) $until = $today;
        if ($since > $today) $since = $today;

        if (empty($identifiers)) {
            return [];
        }

        // extract path from url
        $page_paths = [];
        foreach ($identifiers as $url) {
            $path = parse_url('https://' . $url, PHP_URL_PATH);
            $page_paths[] = $path;
        }

        $formatted = [];
        $url = $this->url_report . $this->property_id . ':runReport';
        $token = $this->get_access_token();

        // GA4 restricts requests to a maximum of 9 metrics per call for some nested reports
        $metric_chunks = array_chunk($this->metric_keys, 9);

        foreach ($metric_chunks as $chunk) {
            $body = [
                'dateRanges' => [['startDate' => $since, 'endDate' => $until]],
                'dimensions' => [
                    ['name' => 'hostName'],
                    ['name' => 'pagePath'],
                    ['name' => 'pageTitle'],
                ],
                'metrics' => array_map(function ($k) {
                    return ['name' => $k];
                }, $chunk),
                'dimensionFilter' => [
                    'filter' => [
                        'fieldName' => 'pagePath',
                        'inListFilter' => [
                            'values' => $page_paths,
                            'caseSensitive' => false,
                        ],
                    ],
                ],
                'limit' => 10000,
            ];

            // request report from ga4 api
            $report = $this->CI->request->report($url, $body, $token);
            $rows = $report['rows'] ?? [];

            foreach ($rows as $row) {
                $hostname = $row['dimensionValues'][0]['value'] ?? '';
                $page_path = $row['dimensionValues'][1]['value'] ?? '';

                if (empty($page_path) || $page_path === '(not set)' || $page_path === '/' || $page_path === '')
                    continue;

                $full_url = $hostname . $page_path;

                if (!isset($formatted[$full_url])) {
                    $formatted[$full_url] = [];
                }

                foreach ($chunk as $idx => $metric_key) {
                    $formatted[$full_url][$metric_key] = (float) ($row['metricValues'][$idx]['value'] ?? 0);
                }
            }
        }

        return $formatted;
    }

    // split report into ad_contents and ad_metrics
    private function split_report_data($rows)
    {
        $ad_contents = [];
        $ad_metrics = [];

        foreach ($rows as $row) {
            $hostname = $row['dimensionValues'][0]['value'] ?? '';
            $page_path = $row['dimensionValues'][1]['value'] ?? '';
            $page_title = $row['dimensionValues'][2]['value'] ?? '';

            // skip if page_path is empty or (not set) or /
            if (empty($page_path) || $page_path === '(not set)' || $page_path === '/' || $page_path === '')
                continue;

            $full_url = $hostname . $page_path;

            // add to ad_contents
            $ad_contents[] = [
                'page_path' => $full_url,
                'page_title' => $page_title,
            ];

            // add to ad_metrics
            $ad_metrics[] = [
                'page_path' => $full_url,
                'metrics' => $this->normalize_metrics($row['metricValues']),
            ];
        }

        return [
            'ad_contents' => $ad_contents,
            'ad_metrics' => $ad_metrics,
        ];
    }

    // normalize metrics
    private function normalize_metrics($metric_values)
    {
        $result = [];
        foreach ($this->metric_keys as $i => $key) {
            $result[$key] = (float) ($metric_values[$i]['value'] ?? 0);
        }

        return $result;
    }

    // check if html has element
    private function html_has_element($html, $filter)
    {
        // check if filter is empty
        if (empty($filter)) {
            return false;
        }

        foreach ($filter as $snippet) {
            // normalize single quote to double quote
            $normalized_html = str_replace("='", '="', str_replace("'", '"', $html));
            $normalized_snippet = str_replace("='", '="', str_replace("'", '"', $snippet));

            // check if html has element
            if (stripos($normalized_html, $normalized_snippet) !== false) {
                return true;
            }
        }

        return false;
    }
}
