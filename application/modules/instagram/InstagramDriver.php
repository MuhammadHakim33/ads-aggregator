<?php
require_once APPPATH . 'core/Platform_driver.php';
require_once APPPATH . 'modules/instagram/MetaGraphInstagramClient.php';

class InstagramDriver extends Platform_driver
{
    private MetaGraphInstagramClient $client;

    public function __construct()
    {
        parent::__construct();
        $this->CI->config->load('platforms');

        $cred = $this->CI->Platform_credential_model->get_by_platform('meta');
        $platform_config = $this->CI->config->item('platforms')['instagram'] ?? [];
        $metrics = $platform_config['metrics'] ?? [];
        $reels_metrics = $platform_config['reels_metrics'] ?? [];
        
        // initialize instagram client for meta graph api
        $this->client = new MetaGraphInstagramClient($cred, $this->CI->request, $metrics, $reels_metrics);
    }

    public function name()
    {
        return 'instagram';
    }

    public function fetch_contents($since, $until, $filters = [])
    {
        // request instagram media from meta graph api
        $raw = $this->client->get_media($since, $until, $filters['keywords'] ?? []);
        // map to ad_contents database schema
        return array_map(function($p) {
            return [
                'title' => mb_substr($p['caption'] ?? 'No Caption', 0, 200),
                'content_identifier' => $p['id'],
                'published_at' => isset($p['timestamp']) ? date('Y-m-d H:i:s', strtotime($p['timestamp'])) : null,
            ];
        }, $raw);
    }

    public function fetch_insights($ids)
    {
        $insights = $this->client->get_media_insights($ids);
        
        foreach ($insights as $id => &$metrics) {
            $interactions = $metrics['total_interactions'] ?? 0;
            $reach = $metrics['reach'] ?? 0;
            $metrics['engagement_rate'] = ($reach > 0) ? round(($interactions / $reach) * 100, 2) : 0;
        }

        return $insights;
    }
}
