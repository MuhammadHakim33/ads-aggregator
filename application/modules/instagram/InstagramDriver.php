<?php
require_once APPPATH . 'core/Platform_driver.php';
require_once APPPATH . 'modules/instagram/MetaGraphInstagramClient.php';

class InstagramDriver extends Platform_driver
{
    private MetaGraphInstagramClient $client;

    public function __construct()
    {
        parent::__construct();
        $cred = $this->CI->Platform_credential_model->get_by_platform('meta');
        // initialize instagram client for meta graph api
        $this->client = new MetaGraphInstagramClient($cred, $this->CI->request);
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
                'ad_type' => 'social',
            ];
        }, $raw);
    }

    public function fetch_insights($ids)
    {
        return $this->client->get_media_insights($ids);
    }
}
