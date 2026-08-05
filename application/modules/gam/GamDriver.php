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

    private function base64url_encode(string $data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public function fetch_contents($since, $until, $filters = [])
    {
        $since = $since ?? date('Y-m-d', strtotime('-30 days'));
        $until = $until ?? date('Y-m-d');

        $token = $this->get_access_token();
        $headers = ['Authorization: Bearer ' . $token];

        // create report
        $report_url = $this->base_url . "networks/{$this->network_code}/reports";
        $report_body = [
            'reportDefinition' => [
                'reportType' => 'HISTORICAL',
                'dateRange' => 'CUSTOM_DATE_RANGE',
                'customDateRange' => [
                    'startDate' => $this->parse_date($since),
                    'endDate' => $this->parse_date($until),
                ],
                'dimensions' => ['LINE_ITEM_ID', 'LINE_ITEM_NAME'],
                'metrics' => array_map('strtoupper', $this->metrics),
            ]
        ];

        $report_response = $this->CI->request->report($report_url, $report_body, $token);
        $report_name = $report_response['name'] ?? null;
        if (!$report_name) {
            throw new \RuntimeException('failed to create gam report.');
        }

        // run report
        $run_response = $this->CI->request->report($this->base_url . $report_name . ':run', [], $token);
        $operation_name = $run_response['name'] ?? null;
        if (!$operation_name) {
            throw new \RuntimeException('failed to run gam report.');
        }

        // poll report (max 15 attempts)
        $operation_url = $this->base_url . $operation_name;
        $report_result_name = null;

        for ($i = 0; $i < 15; $i++) {
            $status = $this->CI->request->get($operation_url, [], $headers);
            if (!empty($status['done'])) {
                $report_result_name = $status['response']['reportResult'] ?? null;
                break;
            }
            sleep(1);
        }

        if (!$report_result_name) {
            throw new \RuntimeException('gam report timed out or failed.');
        }

        // fetch rows
        $rows = $this->CI->request->get(
            $this->base_url . $report_result_name . ':fetchRows',
            ['pageSize' => 5000],
            $headers
        )['rows'] ?? [];

        $filtered = [];

        foreach ($rows as $row) {
            // get id and name ads
            $id = $row['dimensionValues'][0]['intValue'] ?? $row['dimensionValues'][0]['stringValue'] ?? null;
            $name = $row['dimensionValues'][1]['stringValue'] ?? 'Untitled Line Item';

            if (!$id)
                continue;

            // get metric
            $item_metrics = [];
            foreach ($this->metrics as $index => $metric_name) {
                $raw_val = $row['metricValueGroups'][0]['primaryValues'][$index] ?? [];
                $val = $this->extract_metric_value($raw_val);

                // convert CTR to percent
                $item_metrics[$metric_name] = (stripos($metric_name, 'ctr') !== false || stripos($metric_name, 'percent') !== false)
                    ? ($val * 100)
                    : $val;
            }

            // save filtered data
            $filtered[] = [
                'title' => mb_substr((string) $name, 0, 200),
                'content_identifier' => (string) $id,
                'published_at' => null,
                'platform' => 'gam',
                'metrics' => $item_metrics,
            ];
        }

        return $filtered;
    }

    public function fetch_insights($since = null, $until = null)
    {
        $since = $since ?? date('Y-m-d', strtotime('-30 days'));
        $until = $until ?? date('Y-m-d');

        $contents = $this->fetch_contents($since, $until);
        $formatted = [];

        foreach ($contents as $item) {
            $id = $item['content_identifier'];
            $formatted[$id] = $item['metrics'] ?? [];
        }

        return $formatted;
    }

    private function parse_date(string $date)
    {
        $t = strtotime($date);
        return [
            'year' => (int) date('Y', $t),
            'month' => (int) date('n', $t),
            'day' => (int) date('j', $t),
        ];
    }

    private function extract_metric_value($val)
    {
        if (isset($val['intValue'])) {
            return (int) $val['intValue'];
        }
        if (isset($val['doubleValue'])) {
            return (float) $val['doubleValue'];
        }
        if (isset($val['stringValue'])) {
            return (float) $val['stringValue'];
        }
        return 0;
    }
}
