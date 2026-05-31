<?php
require_once APPPATH . 'modules/meta/MetaGraphBaseClient.php';

class MetaGraphInstagramClient extends MetaGraphBaseClient
{
    public function get_media($since, $until, $keywords = [])
    {
        $ig_id = $this->credentials['ig_account_id'] ?? '';
        $params = [
            'fields' => 'id,caption,timestamp,permalink',
            'since' => strtotime($since),
            'until' => strtotime($until)
        ];
        
        // request instagram media from meta graph api
        $response = $this->make_request($ig_id . '/media', $params);
        $posts = $response['data'] ?? [];

        // filtering posts based on keyword
        if (!empty($keywords)) {
            $posts = array_filter($posts, function($p) use ($keywords) {
                foreach ($keywords as $kw) {
                    if (stripos($p['caption'] ?? '', $kw) !== false) return true;
                }
                return false;
            });
        }

        return array_values($posts);
    }

    public function get_media_insights($ids)
    {
        $insights = [];
        $metrics = 'reach,saved,shares,likes,comments,total_interactions';
        
        $batch = [];
        foreach ($ids as $id) {
            $batch[] = [
                'method' => 'GET',
                'relative_url' => "{$id}/insights?metric={$metrics}",
            ];
        }

        // request instagram media insights from meta graph api
        $response = $this->make_request('', ['batch' => json_encode($batch)]);
        
        // normalize instagram media insights
        foreach ($response as $res) {
            if ($res['code'] === 200) {
                $body = json_decode($res['body'], TRUE);
                $insights[] = $this->normalize_insights($body['data'] ?? []);
                continue;
            }

            log_message('error', "Instagram API insight error: " . $res['body']);
        }
        
        return $insights;
    }
}
