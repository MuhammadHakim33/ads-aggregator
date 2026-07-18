<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'core/Platform_driver.php';

class GamDriver extends Platform_driver
{
    protected $url_token = 'https://oauth2.googleapis.com/token';
    protected $base_url = 'https://admanager.googleapis.com/v1/';
    protected $scope = 'https://www.googleapis.com/auth/admanager';
    protected $network_code;
    protected $service_account;
    protected $access_token = null;
    protected $token_expires = 0;
    protected $metrics = [];

    public function __construct()
    {
        parent::__construct();
        $this->CI->config->load('platforms');

        $cred = $this->CI->Platform_credential_model->get_by_platform('gam');
        if (empty($cred) || empty($cred['network_code']) || empty($cred['service_account'])) {
            throw new \Exception("credentials for gam are empty or incomplete. please configure the network code and service account json in the database.");
        }

        $this->network_code = $cred['network_code'] ?? '';
        $this->service_account = $cred['service_account'] ?? '';

        $platform_config = $this->CI->config->item('platforms')['gam'] ?? [];
        $this->metrics = $platform_config['metrics'] ?? [];

        if (empty($this->network_code)) {
            throw new \RuntimeException('gam credential is not complete, network_code not found.');
        }

        if (empty($this->service_account['private_key'])) {
            throw new \RuntimeException('gam credential is not complete, private_key not found.');
        }
    }

    public function name()
    {
        return 'gam';
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
        $token = $this->get_access_token();
        $url = $this->base_url . "networks/{$this->network_code}/lineItems";
        $headers = [
            'Authorization: Bearer ' . $token
        ];

        $params = [
            'pageSize' => 1000,
            'filter' => "status = 'DELIVERING' OR status = 'READY'"
        ];

        $response = $this->CI->request->get($url, $params, $headers);
        $raw = $response['lineItems'] ?? [];

        $keywords = $filters['keywords'] ?? [];
        $filtered = [];

        foreach ($raw as $item) {
            $parts = explode('/', $item['name']);
            $id = end($parts);
            $displayName = $item['displayName'] ?? '';

            // filter by keyword
            if (!empty($keywords)) {
                $match = false;
                foreach ($keywords as $kw) {
                    if (stripos($displayName, $kw) !== false) {
                        $match = true;
                        break;
                    }
                }
                if (!$match) {
                    continue;
                }
            }

            $filtered[] = [
                'title' => mb_substr($displayName, 0, 200),
                'content_identifier' => $id,
                'published_at' => isset($item['startTime']) ? date('Y-m-d H:i:s', strtotime($item['startTime'])) : null,
                'platform' => 'gam',
            ];
        }

        return $filtered;
    }

    public function fetch_insights($identifiers, $since = null, $until = null): array
    {
        $since = $since ?? date('Y-m-d', strtotime('-30 days'));
        $until = $until ?? date('Y-m-d');

        if (empty($identifiers)) {
            return [];
        }

        $token = $this->get_access_token();

        // parse date ranges for reportdefinition
        $since_time = strtotime($since);
        $until_time = strtotime($until);

        $dateRange = [
            'startDate' => [
                'year' => (int) date('Y', $since_time),
                'month' => (int) date('n', $since_time),
                'day' => (int) date('j', $since_time)
            ],
            'endDate' => [
                'year' => (int) date('Y', $until_time),
                'month' => (int) date('n', $until_time),
                'day' => (int) date('j', $until_time)
            ]
        ];

        // create report definition
        $report_url = $this->base_url . "networks/{$this->network_code}/reports";
        $report_body = [
            'reportDefinition' => [
                'reportType' => 'HISTORICAL',
                'dateRange' => 'CUSTOM_DATE_RANGE',
                'customDateRange' => $dateRange,
                'dimensions' => ['LINE_ITEM_ID'],
                'metrics' => array_map('strtoupper', $this->metrics)
            ]
        ];

        $report_response = $this->CI->request->report($report_url, $report_body, $token);
        $report_name = $report_response['name'] ?? null;
        if (!$report_name) {
            throw new \RuntimeException('failed to create gam report definition.');
        }

        // run report
        $run_url = $this->base_url . $report_name . ':run';
        $run_response = $this->CI->request->report($run_url, [], $token);
        $operation_name = $run_response['name'] ?? null;
        if (!$operation_name) {
            throw new \RuntimeException('failed to initiate gam report run.');
        }

        // poll report (max 15 attempts)
        $operation_url = $this->base_url . $operation_name;
        $headers = [
            'Authorization: Bearer ' . $token
        ];

        $attempts = 0;
        $done = false;
        $report_result_name = null;

        while ($attempts < 15) {
            $status_response = $this->CI->request->get($operation_url, [], $headers);
            if (isset($status_response['done']) && $status_response['done'] === true) {
                $done = true;
                $report_result_name = $status_response['response']['reportResult'] ?? null;
                break;
            }
            $attempts++;
            sleep(1);
        }

        if (!$done || !$report_result_name) {
            throw new \RuntimeException('gam report execution timed out or failed.');
        }

        // fetch report rows
        $fetch_url = $this->base_url . $report_result_name . ':fetchRows';
        $fetch_params = [
            'pageSize' => 5000
        ];

        $rows_response = $this->CI->request->get($fetch_url, $fetch_params, $headers);
        $rows = $rows_response['rows'] ?? [];

        // map and return metrics matching line items
        $formatted = [];
        $line_item_ids = array_map('strval', $identifiers);

        foreach ($rows as $row) {
            $line_item_id = $row['dimensionValues'][0]['stringList']['values'][0] ?? null;
            if (!$line_item_id || !in_array($line_item_id, $line_item_ids, true)) {
                continue;
            }

            // extract metrics from the first group
            $metric_values = $row['metricValueGroups'][0]['primaryValues'] ?? [];

            $item_metrics = [];
            foreach ($this->metrics as $index => $metric_name) {
                $val = $metric_values[$index] ?? null;
                $extracted = $val ? $this->extract_metric_value($val) : 0;
                
                // apply percent conversion if needed
                if (stripos($metric_name, 'ctr') !== false || stripos($metric_name, 'percent') !== false) {
                    $extracted = $extracted * 100;
                }
                
                $item_metrics[$metric_name] = $extracted;
            }

            $formatted[$line_item_id] = $item_metrics;
        }

        return $formatted;
    }

    private function extract_metric_value($val)
    {
        if (isset($val['intList']['values'][0])) {
            return (int) $val['intList']['values'][0];
        }
        if (isset($val['doubleList']['values'][0])) {
            return (float) $val['doubleList']['values'][0];
        }
        if (isset($val['moneyList']['values'][0])) {
            return (float) ($val['moneyList']['values'][0]['amount'] ?? 0.0);
        }
        return 0;
    }
}
