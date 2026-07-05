<?php
defined('BASEPATH') or exit('No direct script access allowed');

class GamApiClient
{
    protected $url_token = 'https://oauth2.googleapis.com/token';
    protected $base_url = 'https://admanager.googleapis.com/v1/';
    protected $scope = 'https://www.googleapis.com/auth/admanager';
    protected $network_code;
    protected $service_account;
    protected $access_token = null;
    protected $token_expires = 0;
    protected $request;

    public function __construct($credentials, $request)
    {
        $this->network_code = $credentials['network_code'] ?? '';
        $this->service_account = $credentials['service_account'] ?? '';
        $this->request = $request;

        if (empty($this->network_code)) {
            throw new \RuntimeException('GAM credential is not complete, network_code not found.');
        }

        if (empty($this->service_account['private_key'])) {
            throw new \RuntimeException('GAM credential is not complete, private_key not found.');
        }
    }

    private function get_access_token()
    {
        if ($this->access_token && time() < ($this->token_expires - 60)) {
            return $this->access_token;
        }

        $jwt = $this->make_jwt();
        $body = [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ];

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

    public function get_line_items()
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

        $response = $this->request->get($url, $params, $headers);
        return $response['lineItems'] ?? [];
    }

    public function get_line_items_insight($since, $until, $line_item_ids = [])
    {
        if (empty($line_item_ids)) {
            return [];
        }

        $token = $this->get_access_token();

        // parse date ranges for ReportDefinition
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
                'metrics' => [
                    'AD_SERVER_IMPRESSIONS',
                    'AD_SERVER_CLICKS',
                    'AD_SERVER_CTR',
                    'AD_SERVER_REVENUE',
                    'AD_SERVER_PERCENT_IMPRESSIONS',
                    'AD_SERVER_PERCENT_REVENUE',
                    'AD_SERVER_RESPONSES_SERVED',
                    'AD_SERVER_TARGETED_IMPRESSIONS',
                    'AD_SERVER_TARGETED_CLICKS',
                    'AD_SERVER_TRACKED_ADS'
                ]
            ]
        ];

        $report_response = $this->request->report($report_url, $report_body, $token);
        $report_name = $report_response['name'] ?? null;
        if (!$report_name) {
            throw new \RuntimeException('Failed to create GAM report definition.');
        }

        // run report
        $run_url = $this->base_url . $report_name . ':run';
        $run_response = $this->request->report($run_url, [], $token);
        $operation_name = $run_response['name'] ?? null;
        if (!$operation_name) {
            throw new \RuntimeException('Failed to initiate GAM report run.');
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
            $status_response = $this->request->get($operation_url, [], $headers);
            if (isset($status_response['done']) && $status_response['done'] === true) {
                $done = true;
                $report_result_name = $status_response['response']['reportResult'] ?? null;
                break;
            }
            $attempts++;
            sleep(1);
        }

        if (!$done || !$report_result_name) {
            throw new \RuntimeException('GAM report execution timed out or failed.');
        }

        // fetch report rows
        $fetch_url = $this->base_url . $report_result_name . ':fetchRows';
        $fetch_params = [
            'pageSize' => 5000
        ];

        $rows_response = $this->request->get($fetch_url, $fetch_params, $headers);
        $rows = $rows_response['rows'] ?? [];

        // map and return metrics matching line items
        $formatted = [];
        $line_item_ids = array_map('strval', $line_item_ids);

        foreach ($rows as $row) {
            $line_item_id = $row['dimensionValues'][0]['stringList']['values'][0] ?? null;
            if (!$line_item_id || !in_array($line_item_id, $line_item_ids, true)) {
                continue;
            }

            // extract metrics from the first group
            $metric_values = $row['metricValueGroups'][0]['primaryValues'] ?? [];

            $impressions = isset($metric_values[0]) ? $this->extract_metric_value($metric_values[0]) : 0;
            $clicks = isset($metric_values[1]) ? $this->extract_metric_value($metric_values[1]) : 0;
            $ctr = isset($metric_values[2]) ? $this->extract_metric_value($metric_values[2]) : 0.0;
            $revenue = isset($metric_values[3]) ? $this->extract_metric_value($metric_values[3]) : 0.0;
            $percent_impressions = isset($metric_values[4]) ? $this->extract_metric_value($metric_values[4]) : 0.0;
            $percent_revenue = isset($metric_values[5]) ? $this->extract_metric_value($metric_values[5]) : 0.0;
            $responses_served = isset($metric_values[6]) ? $this->extract_metric_value($metric_values[6]) : 0;
            $targeted_impressions = isset($metric_values[7]) ? $this->extract_metric_value($metric_values[7]) : 0;
            $targeted_clicks = isset($metric_values[8]) ? $this->extract_metric_value($metric_values[8]) : 0;
            $tracked_ads = isset($metric_values[9]) ? $this->extract_metric_value($metric_values[9]) : 0;

            $formatted[$line_item_id] = [
                'ad_server_impressions' => $impressions,
                'ad_server_clicks' => $clicks,
                'ad_server_ctr' => $ctr * 100, // convert ratio to percent
                'ad_server_revenue' => $revenue,
                'ad_server_percent_impressions' => $percent_impressions * 100, // convert ratio to percent
                'ad_server_percent_revenue' => $percent_revenue * 100, // convert ratio to percent
                'ad_server_responses_served' => $responses_served,
                'ad_server_targeted_impressions' => $targeted_impressions,
                'ad_server_targeted_clicks' => $targeted_clicks,
                'ad_server_tracked_ads' => $tracked_ads,
            ];
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
