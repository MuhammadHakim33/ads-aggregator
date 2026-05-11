<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Meta_graph
{
    const BASE_URL = 'https://graph.facebook.com/v25.0/';
    
    private $system_user_token;
    private $ig_account_id;
    private $fb_page_id;
    private $pat;   
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->library('request');
        $this->CI->load->model('Platform_credential_model');

        // load credential from database
        $cred = $this->CI->Platform_credential_model->get_by_platform('meta');
        $this->system_user_token = $cred['system_user_token'];
        $this->ig_account_id = $cred['ig_account_id'];
        $this->fb_page_id = $cred['fb_page_id'];
    }

    private function get_page_access_token()
    {
        // check if page access token is exists
        if (!empty($this->pat)) {
            return $this->pat;
        }

        $params = [
            'fields' => 'access_token',
            'access_token' => $this->system_user_token,
        ];
        
        // request page access token from meta graph api
        $url = self::BASE_URL . $this->fb_page_id;
        $data = $this->CI->request->get($url, $params);

        if (empty($data['access_token'])) {
            throw new \RuntimeException('Failed getting access token');
        }

        return $this->pat = $data['access_token'];
    }

    public function get_facebook_posts($since, $until, $keyword_filters = [])
    {
        $pat = $this->get_page_access_token();
        
        $params = [
            'fields' => 'id,message,created_time,permalink_url',
            'since' => $since,
            'until' => $until,
            'access_token' => $pat,
        ];

        // request facebook posts from meta graph api
        $url = self::BASE_URL . $this->fb_page_id . '/feed';
        $response = $this->CI->request->get($url, $params);
        $posts = $response['data'] ?? [];

        // if empty keyword filters, return all posts
        if (empty($keyword_filters)) {
            return $posts;
        }

        // filtering posts based on keyword
        $result = [];
        foreach ($posts as $post) {
            $message = $post['message'] ?? '';
            foreach ($keyword_filters as $kw) {
                // if post message contains keyword, add to result
                if (stripos($message, $kw) !== FALSE) {
                    $result[] = $post;
                    break;
                }
            }
        }

        return $result;
    }

    public function get_facebook_post_insights($ids)
    {
        $pat = $this->get_page_access_token();
        $batch = [];
        
        // set batch request to get facebook posts insights
        foreach ($ids as $id) {
            $metrics = 'post_media_view,post_clicks,post_reactions_by_type_total';
            $batch[] = [
                'method' => 'GET',
                'relative_url' => "{$id}/insights?metric={$metrics}",
            ];
        }

        $params = [
            'access_token' => $pat,
            'batch' => json_encode($batch),
            'include_headers' => false
        ];        
        
        // request facebook posts insights from meta graph api
        $response = $this->CI->request->post(self::BASE_URL, $params);
        
        // normalize facebook posts insights
        $result = [];
        foreach ($response as $index => $item) {
            $id = $ids[$index];

            if ($item['code'] !== 200) {
                log_message('error', "Facebook API insight error {$id}: " . $item['body']);
                $results[$id] = [];
                continue;
            }

            $body = json_decode($item['body'], TRUE);
            $result[$id] = $this->normalize_insights($body['data'] ?? []);
        }

        return $result;
    }

    public function get_instagram_media($since, $until, $keyword_filters = [])
    {
        $params = [
            'fields'       => 'id,caption,timestamp,permalink',
            'since'        => $since,
            'until'        => $until,
            'access_token' => $this->system_user_token,
        ];
        
        // request instagram media from meta graph api
        $url = self::BASE_URL . $this->ig_account_id . '/media';
        $response = $this->CI->request->get($url, $params);
        $posts = $response['data'] ?? [];
        
        // if empty keyword filters, return all posts
        if (empty($keyword_filters)) {
            return $posts;
        }

        // filtering posts based on keyword
        $result = [];
        foreach ($posts as $post) {
            $caption = $post['caption'] ?? '';
            foreach ($keyword_filters as $kw) {
                // if post caption contains keyword, add to result
                if (stripos($caption, $kw) !== FALSE) {
                    $result[] = $post;
                    break;
                }
            }
        }

        return $result;
    }

    public function get_instagram_media_insights($ids)
    {
        // set batch request to get instagram media insights
        $batch  = [];
        foreach ($ids as $id) {
            $metrics = 'reach,saved,shares,likes,comments,total_interactions';
            $batch[] = [
                'method'       => 'GET',
                'relative_url' => "{$id}/insights?metric={$metrics}",
            ];
        }

        $params = [
            'access_token'    => $this->system_user_token,
            'batch'           => json_encode($batch),
            'include_headers' => false
        ];        
        
        // request instagram media insights from meta graph api
        $response = $this->CI->request->post(self::BASE_URL, $params);
        
        // normalize instagram media insights
        $result = [];
        foreach ($response as $index => $item) {
            $id = $ids[$index];

            if ($item['code'] !== 200) {
                log_message('error', "Instagram API insight error {$id}: " . $item['body']);
                $results[$id] = [];
                continue;
            }

            $body = json_decode($item['body'], TRUE);
            $result[$id] = $this->normalize_insights($body['data'] ?? []);
        }

        return $result;
    }

    private function normalize_insights($data)
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