<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'core/Platform_driver.php';
require_once APPPATH . 'modules/gam/GamApiClient.php';

class GamDriver extends Platform_driver
{
    private ?GamApiClient $client = null;

    public function __construct()
    {
        parent::__construct();
        $this->CI->config->load('platforms');

        $cred = $this->CI->Platform_credential_model->get_by_platform('gam');
        if (empty($cred) || empty($cred['network_code']) || empty($cred['service_account'])) {
            throw new \Exception("Credentials for GAM are empty or incomplete. Please configure the Network Code and Service Account JSON in the database.");
        }

        $this->client = new GamApiClient($cred, $this->CI->request);
    }

    public function name()
    {
        return 'gam';
    }

    public function fetch_contents($since, $until, $filters = [])
    {
        $raw = $this->client->get_line_items();
        $keywords = $filters['keywords'] ?? [];
        $filtered = [];

        foreach ($raw as $item) {
            $parts = explode('/', $item['name']);
            $id = end($parts);
            $displayName = $item['displayName'] ?? '';

            // filter
            if (!empty($keywords)) {
                $match = false;
                foreach ($keywords as $kw) {
                    if (stripos($displayName, $kw) !== false) {
                        $match = true;
                        break;
                    }
                }
                if (!$match) {
                    continue;
                }
            }

            $filtered[] = [
                'title' => mb_substr($displayName, 0, 200),
                'content_identifier' => $id,
                'published_at' => isset($item['startTime']) ? date('Y-m-d H:i:s', strtotime($item['startTime'])) : null,
                'platform' => 'gam',
            ];
        }

        return $filtered;
    }

    public function fetch_insights($identifiers, $since = null, $until = null): array
    {
        $since = $since ?? date('Y-m-d', strtotime('-30 days'));
        $until = $until ?? date('Y-m-d');

        return $this->client->get_line_items_insight($since, $until, $identifiers);
    }
}
