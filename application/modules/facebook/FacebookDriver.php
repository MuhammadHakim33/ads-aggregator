<?php
require_once APPPATH . 'core/Platform_driver.php';
require_once APPPATH . 'modules/facebook/MetaGraphFacebookClient.php';

class FacebookDriver extends Platform_driver
{
    private MetaGraphFacebookClient $client;

    public function __construct()
    {
        parent::__construct();
        $this->CI->config->load('platforms');
        
        $credential = $this->CI->Platform_credential_model->get_by_platform('meta');
        if (empty($credential) || empty($credential['fb_page_id'])) {
            throw new \Exception("Credentials for facebook are empty or incomplete.");
        }
        $platform_config = $this->CI->config->item('platforms')['facebook'] ?? [];
        $metrics = $platform_config['metrics'] ?? [];
        
        // initialize facebook client for meta graph api
        $this->client = new MetaGraphFacebookClient($credential, $this->CI->request, $metrics);
    }

    public function name()
    {
        return 'facebook';
    }

    public function fetch_contents($since, $until, $filters = [])
    {
        // request facebook posts from meta graph api
        $raw = $this->client->get_posts($since, $until, $filters['keywords'] ?? []);
        // map to ad_contents database schema
        return array_map(function($p) {
            return [
                'title' => mb_substr($p['message'] ?? 'No Caption', 0, 200),
                'content_identifier' => $p['id'],
                'published_at' => isset($p['created_time']) ? date('Y-m-d H:i:s', strtotime($p['created_time'])) : null,
                'platform' => 'facebook',
            ];
        }, $raw);
    }

    public function fetch_insights($identifiers)
    {
        $insights = $this->client->get_post_insights($identifiers);
        
        foreach ($insights as $id => &$metrics) {
            $engaged = $metrics['post_engaged_users'] ?? 0;
            $reach = $metrics['post_impressions_unique'] ?? 0;
            $metrics['engagement_rate'] = ($reach > 0) ? round(($engaged / $reach) * 100, 2) : 0;
        }

        return $insights;
    }
}
