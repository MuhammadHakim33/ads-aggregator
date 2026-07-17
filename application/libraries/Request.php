<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Request
{
    private $ca_bundle_path = '';

    public function get($url, $params = [], $headers = [])
    {
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $headers = array_merge(['Accept: application/json'], $headers);
        return $this->execute($url, [
            CURLOPT_HTTPGET => TRUE,
            CURLOPT_HTTPHEADER => $headers,
        ]);
    }

    public function post($url, $params = [], $body = [], $headers = [])
    {
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $headers = array_merge([
            'Accept: application/json',
            'Content-Type: application/json'
        ], $headers);

        return $this->execute($url, [
            CURLOPT_POST => TRUE,
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_HTTPHEADER => $headers,
        ]);
    }

    public function post_form($url, $params = [], $body = [], $headers = [])
    {
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $headers = array_merge([
            'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded'
        ], $headers);

        return $this->execute($url, [
            CURLOPT_POST => TRUE,
            CURLOPT_POSTFIELDS => http_build_query($body),
            CURLOPT_HTTPHEADER => $headers,
        ]);
    }

    public function report($url, $body = [], $token = '')
    {
        $headers = [
            'Authorization: Bearer ' . $token,
            'Accept: application/json',
            'Content-Type: application/json',
        ];

        return $this->execute($url, [
            CURLOPT_POST => TRUE,
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_HTTPHEADER => $headers,
        ]);
    }

    public function scrape($url)
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_FOLLOWLOCATION => TRUE,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => TRUE,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; GA4Bot/1.0)',
            CURLOPT_HTTPHEADER     => ['Accept: text/html'],
        ]);

        if (!empty($this->ca_bundle_path)) {
            curl_setopt($ch, CURLOPT_CAINFO, $this->ca_bundle_path);
        }

        $html      = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error     = curl_error($ch);
        curl_close($ch);

        if ($error || $http_code !== 200 || empty($html)) {
            throw new \RuntimeException("Scrape Error ($url): HTTP $http_code - $error");
        }

        return $html;
    }

    private function execute($url, array $options)
    {
        $ch = curl_init();

        $default_options = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => TRUE, // Wajib TRUE di production
            CURLOPT_SSL_VERIFYHOST => 2,
        ];

        if (!empty($this->ca_bundle_path)) {
            $default_options[CURLOPT_CAINFO] = $this->ca_bundle_path;
        }

        curl_setopt_array($ch, $options + $default_options);

        $response   = curl_exec($ch);
        $http_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($curl_error) {
            throw new \RuntimeException("cURL error ($url): $curl_error");
        }

        $data = json_decode($response, TRUE);

        if ($http_code !== 200 || isset($data['error'])) {
            $message = "HTTP $http_code";
            if (isset($data['error'])) {
                if (is_string($data['error'])) {
                    $message = $data['error_description'] ?? $data['error'];
                } else {
                    $message = $data['error']['message'] ?? $message;
                }
            }
            throw new \RuntimeException("API Error: ($url) - $message");
        }

        return $data;
    }
}