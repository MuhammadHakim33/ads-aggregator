<?php
require_once APPPATH . 'modules/meta/MetaGraphBaseClient.php';

class MetaGraphInstagramClient extends MetaGraphBaseClient
{
    protected $reels_metrics = [];

    public function __construct($credentials, $request, $metrics = [], $reels_metrics = [])
    {
        parent::__construct($credentials, $request, $metrics);
        $this->reels_metrics = $reels_metrics;
    }

    public function get_media($since, $until, $keywords = [])
    {
        $ig_id = $this->credentials['ig_account_id'] ?? '';
        $params = [
            'fields' => 'id,caption,timestamp,permalink',
            'since' => strtotime($since),
            'until' => strtotime($until)
        ];
        
        // request instagram media from meta graph api
        $response = $this->make_request('get', $ig_id . '/media', $params);
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
        
        // standard metrics fallback
        $standard_metrics = !empty($this->metrics) ? implode(',', $this->metrics) : 'reach,saved,shares,likes,comments,total_interactions,profile_activity';
        
        $batch = [];
        foreach ($ids as $id) {
            $batch[] = [
                'method' => 'GET',
                'relative_url' => "{$id}?fields=media_product_type,insights.metric({$standard_metrics})",
            ];
        }

        // request instagram media details and insights from meta graph api
        $response = $this->make_request('post', '', ['batch' => json_encode($batch)]);
        
        $reels_ids = [];
        // normalize standard insights and identify Reels posts
        foreach ($response as $index => $res) {
            if ($res['code'] === 200) {
                $id = $ids[$index];
                $body = json_decode($res['body'], TRUE);
                
                // save standard insights
                $insights[$id] = $this->normalize_insights($body['insights']['data'] ?? []);
                
                // check if the media is Reels
                $product_type = $body['media_product_type'] ?? '';
                if ($product_type === 'REELS') {
                    $reels_ids[] = $id;
                }
                continue;
            }

            log_message('error', "Instagram API media insights error: " . $res['body']);
        }

        // fetch Reels specific insights safely only for Reels media
        if (!empty($reels_ids)) {
            $reels_metrics_str = !empty($this->reels_metrics) ? implode(',', $this->reels_metrics) : 'ig_reels_avg_watch_time,ig_reels_video_view_total_time,reels_skip_rate';
            $reels_batch = [];
            foreach ($reels_ids as $id) {
                $reels_batch[] = [
                    'method' => 'GET',
                    'relative_url' => "{$id}/insights?metric={$reels_metrics_str}",
                ];
            }

            $reels_response = $this->make_request('post', '', ['batch' => json_encode($reels_batch)]);
            
            foreach ($reels_response as $index => $res) {
                if ($res['code'] === 200) {
                    $id = $reels_ids[$index];
                    $body = json_decode($res['body'], TRUE);
                    $reels_insights = $this->normalize_insights($body['data'] ?? []);
                    
                    // merge standard insights with reels specific insights
                    $insights[$id] = array_merge($insights[$id], $reels_insights);
                    continue;
                }

                log_message('error', "Instagram Reels API insights error: " . $res['body']);
            }
        }
        
        return $insights;
    }
}
