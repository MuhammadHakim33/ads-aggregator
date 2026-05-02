<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Request
{
    public function get($url, $params = [])
    {
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
        ]);

        $response   = curl_exec($ch);
        $http_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($curl_error) {
            throw new \RuntimeException("cURL error: $curl_error");
        }

        $data = json_decode($response, TRUE);

        if ($http_code !== 200 || isset($data['error'])) {
            $message = $data['error']['message'] ?? "HTTP $http_code";
            throw new \RuntimeException("API Error: ($url) - $message");
        }

        return $data;
    }

    public function post($url, $params = [], $body = [])
    {
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => TRUE,
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
        ]);

        $response   = curl_exec($ch);
        $http_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($curl_error) {
            throw new \RuntimeException("cURL error: $curl_error");
        }

        $data = json_decode($response, TRUE);

        if ($http_code !== 200 || isset($data['error'])) {
            $message = $data['error']['message'] ?? "HTTP $http_code";
            throw new \RuntimeException("API Error: ($url) - $message");
        }

        return $data;
    }

    public function post_form($url, $params = [], $body = [])
    {
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => TRUE,
            CURLOPT_POSTFIELDS => http_build_query($body),
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
        ]);

        $response   = curl_exec($ch);
        $http_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($curl_error) {
            throw new \RuntimeException("cURL error: $curl_error");
        }

        $data = json_decode($response, TRUE);

        if ($http_code !== 200 || isset($data['error'])) {
            $message = $data['error']['message'] ?? "HTTP $http_code";
            throw new \RuntimeException("API Error: ($url) - $message");
        }

        return $data;
    }

    public function report($url, $body = [], $token)
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => TRUE,
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
            ],
        ]);

        $response   = curl_exec($ch);
        $http_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($curl_error) {
            throw new \RuntimeException("cURL error: $curl_error");
        }

        $data = json_decode($response, TRUE);

        if ($http_code !== 200 || isset($data['error'])) {
            $message = $data['error']['message'] ?? "HTTP $http_code";
            throw new \RuntimeException("API Error: ($url) - $message");
        }

        return $data;
    }

    // request html from url
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

        $html      = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error     = curl_error($ch);
        curl_close($ch);

        if ($error || $http_code !== 200 || empty($html)) {
            return NULL;
        }

        return $html;
    }
}