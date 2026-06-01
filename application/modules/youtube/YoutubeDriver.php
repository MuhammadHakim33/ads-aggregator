<?php
require_once APPPATH . 'core/Platform_driver.php';
require_once APPPATH . 'modules/youtube/YoutubeApiClient.php';

class YoutubeDriver extends Platform_driver
{
    private YoutubeApiClient $client;

    public function __construct()
    {
        parent::__construct();
        $this->CI->config->load('platforms');
        
        $cred = $this->CI->Platform_credential_model->get_by_platform('youtube');
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
        // request youtube video from youtube api
        $raw = $this->client->get_videos($since, $until, $filters['keywords'] ?? []);
        // map to ad_contents database schema
        return array_map(function($p) {
            return [
                'title' => mb_substr($p['snippet']['title'] ?? '', 0, 200),
                'content_identifier' => $p['id'] ?? '',
                'ad_type' => 'video',
                'platform' => 'youtube',
            ];
        }, $raw);
    }

    public function fetch_insights($identifiers)
    {
        return $this->client->get_video_stats($identifiers);
    }
}
