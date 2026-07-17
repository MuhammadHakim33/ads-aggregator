<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Request
{
    private string $ca_bundle_path;

    public function __construct(string $ca_bundle_path = '')
    {
        if (!empty($ca_bundle_path)) {
            $this->ca_bundle_path = $ca_bundle_path;
            return;
        }

        $php_ini_cainfo = ini_get('curl.cainfo');
        if (!empty($php_ini_cainfo) && file_exists($php_ini_cainfo)) {
            $this->ca_bundle_path = $php_ini_cainfo;
            return;
        }

        // auto-detect ssl certs
        $common_paths = [
            '/etc/ssl/certs/ca-certificates.crt',
            '/etc/pki/tls/certs/ca-bundle.crt',
            '/usr/local/etc/openssl/cert.pem',  
            '/etc/ssl/cert.pem',                
        ];
        foreach ($common_paths as $path) {
            if (file_exists($path)) {
                $this->ca_bundle_path = $path;
                return;
            }
        }

        // fallback
        $fallback = APPPATH . 'third_party/cacert.pem';
        if (file_exists($fallback)) {
            $this->ca_bundle_path = $fallback;
            return;
        }

        throw new \RuntimeException(
            'CA bundle tidak ditemukan. Download dari https://curl.se/ca/cacert.pem ' .
            'dan simpan ke application/third_party/cacert.pem'
        );
    }

    public function get(string $url, array $params = [], array $headers = []): ?array
    {
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $this->execute($url, [
            CURLOPT_HTTPGET    => TRUE,
            CURLOPT_HTTPHEADER => array_merge(['Accept: application/json'], $headers),
        ]);
    }

    public function post(string $url, array $params = [], array $body = [], array $headers = []): ?array
    {
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $this->execute($url, [
            CURLOPT_POST       => TRUE,
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_HTTPHEADER => array_merge([
                'Accept: application/json',
                'Content-Type: application/json',
            ], $headers),
        ]);
    }

    public function post_form(string $url, array $params = [], array $body = [], array $headers = []): ?array
    {
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $this->execute($url, [
            CURLOPT_POST       => TRUE,
            CURLOPT_POSTFIELDS => http_build_query($body),
            CURLOPT_HTTPHEADER => array_merge([
                'Accept: application/json',
                'Content-Type: application/x-www-form-urlencoded',
            ], $headers),
        ]);
    }

    public function report(string $url, array $body = [], string $token = ''): ?array
    {
        return $this->execute($url, [
            CURLOPT_POST       => TRUE,
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token,
                'Accept: application/json',
                'Content-Type: application/json',
            ],
        ]);
    }

    public function scrape(string $url): ?string
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_FOLLOWLOCATION => TRUE,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => TRUE,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_CAINFO         => $this->ca_bundle_path,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; GA4Bot/1.0)',
            CURLOPT_HTTPHEADER     => ['Accept: text/html'],
        ]);

        $html      = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error     = curl_error($ch);
        curl_close($ch);

        if ($error || $http_code < 200 || $http_code >= 300 || empty($html)) {
            throw new \RuntimeException("Scrape Error ($url): HTTP $http_code - $error");
        }

        return $html;
    }

    private function execute(string $url, array $options): ?array
    {
        $ch = curl_init();

        curl_setopt_array($ch, $options + [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => TRUE,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_CAINFO         => $this->ca_bundle_path,
        ]);

        $response   = curl_exec($ch);
        $http_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($curl_error) {
            throw new \RuntimeException("cURL error ($url): $curl_error");
        }

        if ($http_code === 204 || empty($response)) {
            if ($http_code >= 200 && $http_code < 300) {
                return null;
            }
            throw new \RuntimeException("API Error ($url): HTTP $http_code (empty response)");
        }

        $data = json_decode($response, TRUE);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException(
                "Invalid JSON dari $url: " . substr($response, 0, 150)
            );
        }

        if ($http_code < 200 || $http_code >= 300 || isset($data['error'])) {
            $message = "HTTP $http_code";
            if (isset($data['error'])) {
                $message = is_string($data['error'])
                    ? ($data['error_description'] ?? $data['error'])
                    : ($data['error']['message'] ?? $message);
            }
            throw new \RuntimeException("API Error ($url): $message");
        }

        return $data;
    }
}