<?php
require_once APPPATH . 'core/Platform_driver.php';

abstract class MetaPlatformDriver extends Platform_driver
{
    protected $credentials;
    protected $api_version = 'v25.0';
    protected $base_url = 'https://graph.facebook.com/';
    protected $metrics = [];

    public function __construct()
    {
        parent::__construct();
    }

    protected function get_system_user_token()
    {
        return $this->credentials['system_user_token'] ?? '';
    }

    protected function make_request($method = 'get', $endpoint = '', $params = [], $token_override = null)
    {
        // use token override if provided, otherwise fallback to system user token
        $params['access_token'] = $token_override ?? $this->get_system_user_token();

        $url = $this->base_url . $this->api_version . '/' . $endpoint;

        // execute http request based on method
        switch (strtolower($method)) {
            case 'get':
                $response = $this->CI->request->get($url, $params);
                break;
            case 'post':
                $response = $this->CI->request->post($url, $params);
                break;
            default:
                throw new \Exception("invalid method: " . $method);
        }
        
        // throw exception if meta api returns error
        if (isset($response['error'])) {
            throw new \Exception("meta api error: " . $response['error']['message']);
        }

        return $response;
    }

    protected function normalize_insights($data)
    {
        $result = [];
        // format insight data to key-value pairs
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
