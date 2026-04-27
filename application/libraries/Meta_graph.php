<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Meta_graph
{
    const GRAPH_VERSION = 'v25.0';
    const BASE_URL      = 'https://graph.facebook.com/';

    private $system_user_token;
    private $fb_page_id;
    private $ig_account_id;
    private $pat;

    public function __construct()
    {
        $this->system_user_token = $_ENV['META_SYSTEM_USER_TOKEN'];
        $this->fb_page_id        = $_ENV['META_FB_PAGE_ID'];
        $this->ig_account_id     = $_ENV['META_IG_ACCOUNT_ID'];
    }

    private function request($endpoint, $params = [], $method = 'GET')
    {
        $url = self::BASE_URL . self::GRAPH_VERSION . '/' . ltrim($endpoint, '/');

        $ch = curl_init();

        if ($method === 'GET') {
            $url .= '?' . http_build_query($params);
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response  = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($curl_error) throw new \RuntimeException("cURL error: $curl_error");

        $data = json_decode($response, TRUE);

        if ($http_code !== 200 || isset($data['error'])) {
            $message = $data['error']['message'] ?? "HTTP $http_code";
            throw new \RuntimeException("Meta API error: $message");
        }

        return $data;
    }

    private function get_page_access_token()
    {
        if (!empty($this->pat)) return $this->pat;

        $data = $this->request($this->fb_page_id, [
            'fields'       => 'access_token',
            'access_token' => $this->system_user_token,
        ]);

        if (empty($data['access_token'])) {
            throw new \RuntimeException('Failed getting access token');
        }

        return $this->pat = $data['access_token'];
    }

    public function get_facebook_posts($since, $until, $keyword_filter = '')
    {
        $pat    = $this->get_page_access_token();
        $result = [];

        $params = [
            'fields'       => 'id,message,created_time,permalink_url',
            'since'        => $since,
            'until'        => $until,
            'access_token' => $pat,
        ];

        $response = $this->request($this->fb_page_id . '/feed', $params);
        $posts    = $response['data'] ?? [];

        foreach ($posts as $post) {
            if (!empty($keyword_filter) && stripos($post['message'] ?? '', $keyword_filter) === FALSE) {
                continue;
            }

            $result[] = $post;
        }

        return $result;
    }

    public function get_facebook_post_insights($ids)
    {
        $pat    = $this->get_page_access_token();
        $batch  = [];
        $result = [];

        foreach ($ids as $id) {
            $metrics = 'post_media_view,post_clicks,post_reactions_by_type_total';
            $batch[] = [
                'method'       => 'GET',
                'relative_url' => "{$id}/insights?metric={$metrics}",
            ];
        }

        $params = [
            'access_token'    => $pat,
            'batch'           => json_encode($batch),
            'include_headers' => false
        ];        

        $response = $this->request('', $params, 'POST');

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

    public function get_instagram_media($since, $until, $keyword_filter = '')
    {
        $result = [];

        $params = [
            'fields'       => 'id,caption,timestamp,permalink',
            'since'        => $since,
            'until'        => $until,
            'access_token' => $this->system_user_token,
        ];

        $response = $this->request($this->ig_account_id . '/media', $params);
        $posts    = $response['data'] ?? [];

        foreach ($posts as $post) {
            if (!empty($keyword_filter) && stripos($post['caption'] ?? '', $keyword_filter) === FALSE) {
                continue;
            }

            $result[] = $post;
        }

        return $result;
    }

    public function get_instagram_media_insights($ids)
    {
        $batch  = [];
        $result = [];

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

        $response = $this->request('', $params, 'POST');

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
            } 
            else {
                $result[$name] = $value;
            }
        }
        return $result;
    }
}