<?php
require_once APPPATH . 'core/Platform_driver.php';
require_once APPPATH . 'modules/ga4/Ga4ApiClient.php';

class Ga4Driver extends Platform_driver
{
    private ?Ga4ApiClient $client = null;
    private bool $is_configured = true;

    public function __construct()
    {
        parent::__construct();
        $this->CI->config->load('platforms');

        $credentials = $this->CI->Platform_credential_model->get_by_platform('ga4');
        if (empty($credentials) || empty($credentials['property_id']) || empty($credentials['service_account']['private_key'])) {
            $this->is_configured = false;
            log_message('error', '[GA4] Credentials are empty or incomplete. Skipping fetch/sync.');
            echo "[ga4] WARNING: Credentials are empty or incomplete. Skipping.\n";
            return;
        }
        
        $platform_config = $this->CI->config->item('platforms')['ga4'] ?? [];
        $metrics = $platform_config['metrics'] ?? [];
        
        // initialize GA4 api client
        $this->client = new Ga4ApiClient($credentials, $this->CI->request, $metrics);
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
        if (!$this->is_configured) {
            return [];
        }
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
                'platform' => 'ga4',
            ];
        }, $raw);
    }

    public function fetch_insights($identifiers, $since = null, $until = null): array
    {
        if (!$this->is_configured) {
            return [];
        }
        $since = $since ?? date('Y-m-d', strtotime('-30 days'));
        $until = $until ?? date('Y-m-d');
        
        $result = $this->client->get_articles_insight($since, $until, $identifiers);
        $ad_metrics = $result['ad_metrics'] ?? [];
        
        $formatted = [];
        foreach ($ad_metrics as $item) {
            $formatted[$item['page_path']] = $item['metrics'];
        }

        return $formatted;
    }
}
