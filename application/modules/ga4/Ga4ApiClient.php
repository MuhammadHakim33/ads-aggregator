<?php

class Ga4ApiClient
{
    protected $url_token = 'https://oauth2.googleapis.com/token';
    protected $url_report = 'https://analyticsdata.googleapis.com/v1beta/properties/';
    protected $scope = 'https://www.googleapis.com/auth/analytics.readonly';
    protected $metric_keys = [
        'screenPageViews',
        'activeUsers',
        'averageSessionDuration',
        'bounceRate',
        'sessions',
    ];
    protected $property_id;
    protected $service_account;
    protected $access_token = null;
    protected $token_expires = 0;
    protected $request;

    public function __construct($credentials, $request)
    {
        $data = $credentials;
        $this->property_id = $data['property_id'] ?? '';
        $this->service_account = $data['service_account'] ?? '';
        $this->request = $request;

        if (empty($this->service_account['private_key'])) {
            throw new \RuntimeException('GA4 credential is not complete, private_key not found.');
        }
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
        $response = $this->request->post_form($this->url_token, [], $body);

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

    public function get_articles($since, $until, $hostnames = [], $html_filter = [])
    {
        $body = [
            'dateRanges' => [['startDate' => $since, 'endDate' => $until]],
            'dimensions' => [
                ['name' => 'hostName'],
                ['name' => 'pagePath'],
                ['name' => 'pageTitle'],
            ],
            'metrics' => array_map(fn($k) => ['name' => $k], $this->metric_keys),
            'limit' => 10000,
        ];

        // send request to get report
        $url = $this->url_report . $this->property_id . ':runReport';
        $token = $this->get_access_token();
        $report = $this->request->report($url, $body, $token);
        $rows = $report['rows'] ?? [];

        // if no filter, return all rows
        if (empty($hostnames) && empty($html_filter)) {
            return $this->split($rows);
        }

        // normalize hostnames to lowercase
        $hostnames = array_map('strtolower', $hostnames);

        // filter rows based on hostname match or html element scraping
        $filtered_rows = [];
        foreach ($rows as $row) {
            $hostname  = strtolower($row['dimensionValues'][0]['value'] ?? '');
            $page_path = $row['dimensionValues'][1]['value'] ?? '';

            // filter 1: hostname match
            if (!empty($hostnames)) {
                if (in_array($hostname, $hostnames, true)) {
                    $filtered_rows[] = $row;
                    continue;
                }
            }

            // filter 2: html element scraping
            if (!empty($html_filter)) {
                $full_url = 'https://' . $hostname . $page_path;
                $html = $this->request->scrape($full_url);

                if ($html !== null && $this->html_has_element($html, $html_filter)) {
                    $filtered_rows[] = $row;
                }
            }
        }

        return $this->split($filtered_rows);
    }

    public function get_articles_insight($since, $until, $urls = [])
    {
        if (empty($urls)) {
            return [];
        }

        // extract path from url
        $page_paths = [];
        foreach ($urls as $url) {
            $path = parse_url('https://' . $url, PHP_URL_PATH);
            $page_paths[] = $path;
        }

        $body = [
            'dateRanges' => [['startDate' => $since, 'endDate' => $until]],
            'dimensions' => [
                ['name' => 'hostName'],
                ['name' => 'pagePath'],
                ['name' => 'pageTitle'],
            ],
            'metrics' => array_map(fn($k) => ['name' => $k], $this->metric_keys),
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

        // request report from GA4 API
        $url = $this->url_report . $this->property_id . ':runReport';
        $token = $this->get_access_token();
        $report = $this->request->report($url, $body, $token);

        // split report into ad_contents and ad_metrics
        return $this->split($report['rows'] ?? []);
    }

    // split report into ad_contents and ad_metrics
    private function split($rows)
    {
        $ad_contents = [];
        $ad_metrics = [];

        foreach ($rows as $row) {
            $hostname = $row['dimensionValues'][0]['value'] ?? '';
            $page_path = $row['dimensionValues'][1]['value'] ?? '';
            $page_title = $row['dimensionValues'][2]['value'] ?? '';

            // skip if page_path is empty or (not set) or /
            if (empty($page_path) || $page_path === '(not set)' || $page_path === '/' || $page_path === '') continue;

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
            'ad_metrics'  => $ad_metrics,
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
