<?php
require_once APPPATH . 'core/Platform_driver.php';
require_once APPPATH . 'modules/ga4/Ga4ApiClient.php';

class Ga4Driver extends Platform_driver
{
    private Ga4ApiClient $client;

    public function __construct()
    {
        parent::__construct();
        $credentials = $this->CI->Platform_credential_model->get_by_platform('ga4');
        // initialize GA4 api client
        $this->client = new Ga4ApiClient($credentials, $this->CI->request);
    }

    public function name() 
    { 
        return 'ga4'; 
    }

    public function supports_hostname_filter() 
    { 
        return true; 
    }
    
    public function supports_html_filter() 
    { 
        return true; 
    }

    public function fetch_contents($since, $until, $filters = [])
    {
        $result = $this->client->get_articles(
            $since,
            $until,
            $filters['hostnames'] ?? [],
            $filters['html'] ?? []
        );

        $raw = $result['ad_contents'] ?? [];

        // map to ad_contents database schema
        return array_map(function($article) {
            return [
                'title' => mb_substr($article['page_title'] ?? '', 0, 200),
                'content_identifier' => $article['page_path'],
                'ad_type' => 'article',
                'platform' => 'ga4',
            ];
        }, $raw);
    }

    public function fetch_insights($identifiers, $since = null, $until = null): array
    {
        $since = $since ?? date('Y-m-d', strtotime('-30 days'));
        $until = $until ?? date('Y-m-d');
        
        $result = $this->client->sync_articles_insight($since, $until, $identifiers);
        $ad_metrics = $result['ad_metrics'] ?? [];
        
        $formatted = [];
        foreach ($ad_metrics as $item) {
            $formatted[$item['page_path']] = $item['metrics'];
        }

        return $formatted;
    }
}
