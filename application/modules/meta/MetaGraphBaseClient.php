<?php

abstract class MetaGraphBaseClient
{
    protected $credentials;
    protected $request;
    protected $api_version = 'v25.0';
    protected $base_url = 'https://graph.facebook.com/';

    public function __construct($credentials, $request)
    {
        $this->credentials = json_decode($credentials->credential_data, true);
        $this->request = $request;
    }

    protected function get_system_user_token()
    {
        return $this->credentials['system_user_token'] ?? '';
    }

    protected function make_request($endpoint, $params = [], $token_override = null)
    {
        // default using system user token
        $params['access_token'] = $token_override ?? $this->get_system_user_token();

        $url = $this->base_url . $this->api_version . '/' . $endpoint;

        // make request
        $response = $this->request->get($url, $params);
        
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
