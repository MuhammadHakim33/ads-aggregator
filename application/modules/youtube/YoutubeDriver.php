<?php
require_once APPPATH . 'core/Platform_driver.php';
require_once APPPATH . 'modules/youtube/YoutubeApiClient.php';

class YoutubeDriver extends Platform_driver
{
    private ?YoutubeApiClient $client = null;

    public function __construct()
    {
        parent::__construct();
        $this->CI->config->load('platforms');

        $cred = $this->CI->Platform_credential_model->get_by_platform('youtube');
        if (empty($cred) || empty($cred['client_id']) || empty($cred['client_secret']) || empty($cred['channel_id'])) {
            throw new \Exception("Credentials for youtube are empty or incomplete. Please ensure client_id, client_secret, and channel_id are set in the database.");
        }
        
        if (empty($cred['access_token']) || empty($cred['refresh_token'])) {
            throw new \Exception("YouTube OAuth tokens are missing. Please authorize the application by navigating to /youtube_oauth/login.");
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
        // request youtube video from youtube api
        $raw = $this->client->get_videos($since, $until, $filters['keywords'] ?? []);
        // map to ad_contents database schema
        return array_map(function ($p) {
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
