<?php
require_once APPPATH . 'core/Platform_driver.php';
require_once APPPATH . 'exceptions/PartialSuccessException.php';

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
        if ($until > $today)
            $until = $today;
        if ($since > $today)
            $since = $today;

        // get access token
        $token = $this->get_access_token();

        // request API Google Analytic
        $url = $this->url_report . $this->property_id . ':runReport';
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

        $report = $this->CI->request->report($url, $body, $token);
        $rows = $report['rows'] ?? [];

        log_message('info', '[fetch data ga4] ' . json_encode($rows));

        // filter hostname
        $hostnames = array_map('strtolower', $filters['hostnames'] ?? []);
        if (!empty($hostnames)) {
            $rows = array_filter($rows, function ($row) use ($hostnames) {
                $hostname = strtolower($row['dimensionValues'][0]['value'] ?? '');
                return in_array($hostname, $hostnames, true);
            });
        }

        // filter html
        $html_filter = $filters['html'] ?? [];
        if (!empty($html_filter)) {
            $rows = array_filter($rows, function ($row) use ($html_filter) {
                $hostname = strtolower($row['dimensionValues'][0]['value'] ?? '');
                $page_path = $row['dimensionValues'][1]['value'] ?? '';

                if (empty($page_path) || $page_path === '(not set)' || $page_path === '/') {
                    return false;
                }

                try {
                    $html = $this->CI->request->scrape('https://' . $hostname . $page_path);
                    return $this->html_has_element($html, $html_filter);
                } catch (\Throwable $e) {
                    log_message('error', "[Ga4Driver::fetch_contents] Scrape error: " . $e->getMessage());
                    return false;
                }
            });
        }

        // Map to ad_contents database schema
        $result = $this->split_report_data($rows);
        $raw = $result['ad_contents'] ?? [];

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
        if ($until > $today)
            $until = $today;
        if ($since > $today)
            $since = $today;

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

        // normalize html: remove all spaces, newlines, tabs, and convert to lowercase
        $normalized_html = preg_replace('/\s+/', '', strtolower($html));
        $normalized_html = str_replace(["'", '"'], '', $normalized_html);

        foreach ($filter as $snippet) {
            $normalized_snippet = preg_replace('/\s+/', '', strtolower($snippet));
            $normalized_snippet = str_replace(["'", '"'], '', $normalized_snippet);

            // check if html has element
            if (strpos($normalized_html, $normalized_snippet) !== false) {
                return true;
            }
        }

        return false;
    }
}
