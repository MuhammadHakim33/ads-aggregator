<?php

abstract class MetaGraphBaseClient
{
    protected $credentials;
    protected $request;
    protected $api_version = 'v25.0';
    protected $base_url = 'https://graph.facebook.com/';
    protected $metrics = [];

    public function __construct($credentials, $request, $metrics = [])
    {
        $this->credentials = $credentials;
        $this->request = $request;
        $this->metrics = $metrics;
    }

    protected function get_system_user_token()
    {
        return $this->credentials['system_user_token'] ?? '';
    }

    protected function make_request($method = 'get', $endpoint, $params = [], $token_override = null)
    {
        // default using system user token
        $params['access_token'] = $token_override ?? $this->get_system_user_token();

        $url = $this->base_url . $this->api_version . '/' . $endpoint;

        // make request
        switch ($method) {
            case 'get':
                $response = $this->request->get($url, $params);
                break;
            case 'post':
                $response = $this->request->post($url, $params);
                break;
            default:
                throw new \Exception("Invalid method: " . $method);
        }
        
        if (isset($response['error'])) {
            throw new \Exception("Meta API Error: " . $response['error']['message']);
        }

        return $response;
    }

    protected function normalize_insights($data)
    {
        $result = [];
        foreach ($data as $metric) {
            $name  = $metric['name'];
            $value = $metric['values'][0]['value'] ?? $metric['value'] ?? 0;

            if (is_array($value)) {
                $result[$name] = array_sum($value);
            } else {
                $result[$name] = $value;
            }
        }
        
        return $result;
    }
}
