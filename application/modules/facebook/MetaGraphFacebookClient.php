<?php
require_once APPPATH . 'modules/meta/MetaGraphBaseClient.php';

class MetaGraphFacebookClient extends MetaGraphBaseClient
{
    private $pat;

    private function get_page_access_token()
    {
        if (!empty($this->pat)) {
            return $this->pat;
        }

        $page_id = $this->credentials['fb_page_id'] ?? '';
        
        // get page access token from meta graph api
        $response = $this->make_request('get', $page_id, ['fields' => 'access_token']);
        
        if (empty($response['access_token'])) {
            throw new \RuntimeException('Failed getting page access token');
        }

        return $this->pat = $response['access_token'];
    }

    public function get_posts($since, $until, $keywords = [])
    {
        $page_id = $this->credentials['fb_page_id'] ?? '';
        $pat = $this->get_page_access_token();

        $params = [
            'fields' => 'id,message,created_time,permalink_url',
            'since' => strtotime($since), 
            'until' => strtotime($until)
        ];
        
        // passing $pat for overriding default system user token
        $response = $this->make_request('get', $page_id . '/feed', $params, $pat);
        $posts = $response['data'] ?? [];

        // filtering posts based on keyword
        if (!empty($keywords)) {
            $posts = array_filter($posts, function($p) use ($keywords) {
                foreach ($keywords as $kw) {
                    if (stripos($p['message'] ?? '', $kw) !== false) return true;
                }
                return false;
            });
        }

        return array_values($posts);
    }

    public function get_post_insights($ids)
    {
        $insights = [];
        $metrics = !empty($this->metrics) ? implode(',', $this->metrics) : 'post_media_view,post_clicks,post_reactions_by_type_total';
        $pat = $this->get_page_access_token();
        
        $batch = [];
        foreach ($ids as $id) {
            $batch[] = [
                'method' => 'GET',
                'relative_url' => "{$id}/insights?metric={$metrics}",
            ];
        }

        // request facebook posts insights from meta graph api
        $response = $this->make_request('post', '', ['batch' => json_encode($batch)], $pat);
        
        // normalize facebook posts insights
        foreach ($response as $index => $res) {
            if ($res['code'] === 200) {
                $id = $ids[$index];
                $body = json_decode($res['body'], TRUE);
                $insights[$id] = $this->normalize_insights($body['data'] ?? []);
                continue;
            }

            log_message('error', "Facebook API insight error: " . $res['body']);
        }
        
        return $insights;
    }
}
