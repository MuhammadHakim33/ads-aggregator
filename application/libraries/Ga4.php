<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ga4
{
    const TOKEN_ENDPOINT = 'https://oauth2.googleapis.com/token';
    const REPORT_URL = 'https://analyticsdata.googleapis.com/v1beta/properties/';
    const SCOPE = 'https://www.googleapis.com/auth/analytics.readonly';

    private string  $property_id;
    private array   $service_account;
    private ?string $access_token   = null;
    private int     $token_expires  = 0;

    const METRIC_KEYS = [
        'screenPageViews',
        'activeUsers',
        'averageSessionDuration',
        'bounceRate',
        'sessions',
    ];

    public function __construct()
    {
        $json = $_ENV['GA4_SERVICE_ACCOUNT_JSON'];
        $this->property_id = $_ENV['GA4_PROPERTY_ID'];
        $this->service_account = json_decode($json, TRUE);

        if (empty($this->service_account['private_key'])) {
            throw new \RuntimeException('Set GA4_SERVICE_ACCOUNT_JSON environment variable');
        }
    }

    private function request_report($since, $until, $dimension_filter)
    {
        $token = $this->get_access_token();
        $url = self::REPORT_URL . $this->property_id . ':runReport';

        $body = [
            'dateRanges' => [[
                'startDate' => $since,
                'endDate'   => $until,
            ]],
            'dimensions' => [
                ['name' => 'hostName'],
                ['name' => 'pagePath'],
                ['name' => 'pageTitle'],
            ],
            'metrics'         => array_map(fn($k) => ['name' => $k], self::METRIC_KEYS),
            'dimensionFilter' => $dimension_filter,
            'limit'           => 10000,
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => TRUE,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS     => json_encode($body),
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_SSL_VERIFYPEER => TRUE,
            CURLOPT_TIMEOUT        => 30,
        ]);

        $response   = curl_exec($ch);
        $curl_error = curl_error($ch);
        $http_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curl_error) throw new \RuntimeException("cURL error: $curl_error");

        $data = json_decode($response, TRUE);

        if ($http_code !== 200 || isset($data['error'])) {
            $message = $data['error']['message'] ?? "HTTP $http_code";
            throw new \RuntimeException("GA4 API error: $message");
        }

        return $data;
    }

    public function get_access_token()
    {
        if ($this->access_token && time() < ($this->token_expires - 60)) {
            return $this->access_token;
        }

        $jwt = $this->make_jwt();
        $response = $this->exchange_jwt_for_token($jwt);

        $this->access_token = $response['access_token'];
        $this->token_expires = time() + (int) ($response['expires_in'] ?? 3600);

        return $this->access_token;
    }

    private function make_jwt()
    {
        $now = time();

        $header = $this->base64url_encode(json_encode([
            'alg' => 'RS256',
            'typ' => 'JWT',
        ]));

        $payload = $this->base64url_encode(json_encode([
            'iss'   => $this->service_account['client_email'],
            'scope' => self::SCOPE,
            'aud'   => self::TOKEN_ENDPOINT,
            'iat'   => $now,
            'exp'   => $now + 3600,
        ]));

        $data_to_sign = $header . '.' . $payload;

        $private_key = openssl_pkey_get_private($this->service_account['private_key']);
        openssl_sign($data_to_sign, $signature, $private_key, 'SHA256');

        return $data_to_sign . '.' . $this->base64url_encode($signature);
    }

    private function exchange_jwt_for_token($jwt)
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => self::TOKEN_ENDPOINT,
            CURLOPT_POST           => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_SSL_VERIFYPEER => TRUE,
            CURLOPT_POSTFIELDS     => http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]),
        ]);

        $response = curl_exec($ch);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($curl_error) throw new \RuntimeException("JWT exchange cURL error: $curl_error");

        $data = json_decode($response, TRUE);

        if (empty($data['access_token'])) {
            throw new \RuntimeException('Failed getting access token: ' . $response);
        }

        return $data;
    }

    private function base64url_encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public function get_articles($since, $until, $url_pattern)
    {
        $report = $this->request_report($since, $until, [
            'filter' => [
                'fieldName'    => 'pagePath',
                'stringFilter' => [
                    'matchType'     => 'BEGINS_WITH',
                    'value'         => $url_pattern,
                    'caseSensitive' => FALSE,
                ],
            ],
        ]);

        return $this->split($report['rows'] ?? []);
    }

    public function sync_articles_insight($since, $until, $page_paths)
    {
        if (empty($page_paths)) {
            return ['ad_contents' => [], 'ad_metrics' => []];
        }

        $all_rows = [];

        foreach (array_chunk($page_paths, 20) as $chunk) {
            $report = $this->request_report($since, $until, [
                'orGroup' => [
                    'expressions' => array_map(fn($path) => [
                        'filter' => [
                            'fieldName'    => 'pagePath',
                            'stringFilter' => [
                                'matchType' => 'EXACT',
                                'value'     => $path,
                            ],
                        ],
                    ], $chunk),
                ],
            ]);

            $all_rows = array_merge($all_rows, $report['rows'] ?? []);
        }

        return $this->split($all_rows);
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