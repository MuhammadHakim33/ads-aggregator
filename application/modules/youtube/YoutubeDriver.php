<?php
require_once APPPATH . 'core/Platform_driver.php';
require_once APPPATH . 'modules/youtube/YoutubeApiClient.php';

class YoutubeDriver extends Platform_driver
{
    private ?YoutubeApiClient $client = null;
    private bool $is_configured = true;

    public function __construct()
    {
        parent::__construct();
        $this->CI->config->load('platforms');
        
        $cred = $this->CI->Platform_credential_model->get_by_platform('youtube');
        if (empty($cred) || empty($cred['api_key']) || empty($cred['channel_id'])) {
            $this->is_configured = false;
            log_message('error', '[YouTube] Credentials are empty or incomplete. Skipping fetch/sync.');
            echo "[youtube] WARNING: Credentials are empty or incomplete. Skipping.\n";
            return;
        }
        
        $platform_config = $this->CI->config->item('platforms')['youtube'] ?? [];
        $metrics = $platform_config['metrics'] ?? [];
        
        // initialize youtube api client
        $this->client = new YoutubeApiClient($cred, $this->CI->request, $metrics);
    }

    public function name() 
    { 
        return 'youtube'; 
    }

    public function fetch_contents($since, $until, $filters = [])
    {
        if (!$this->is_configured) {
            return [];
        }
        // request youtube video from youtube api
        $raw = $this->client->get_videos($since, $until, $filters['keywords'] ?? []);
        // map to ad_contents database schema
        return array_map(function($p) {
            return [
                'title' => mb_substr($p['snippet']['title'] ?? '', 0, 200),
                'content_identifier' => $p['id'] ?? '',
                'published_at' => isset($p['snippet']['publishedAt']) ? date('Y-m-d H:i:s', strtotime($p['snippet']['publishedAt'])) : null,
                'platform' => 'youtube',
            ];
        }, $raw);
    }

    public function fetch_insights($identifiers)
    {
        if (!$this->is_configured) {
            return [];
        }
        
        $insights = $this->client->get_video_stats($identifiers);
        
        foreach ($insights as $id => &$metrics) {
            $likes = $metrics['likeCount'] ?? 0;
            $comments = $metrics['commentCount'] ?? 0;
            $views = $metrics['viewCount'] ?? 0;
            
            $engagements = $likes + $comments;
            $metrics['engagement_rate'] = ($views > 0) ? round(($engagements / $views) * 100, 2) : 0;
        }

        return $insights;
    }
}
