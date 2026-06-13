<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Platform extends CI_Controller
{
    public function __construct()
    {
        // ensure only cli access
        if (!is_cli()) {
            show_error('This controller can only be accessed via CLI');
        }

        parent::__construct();
        $this->load->library('Platform_registry');
        $this->load->model('Filter_keyword_model');
        $this->load->model('Ad_model');
        $this->load->model('Cron_log_model');
    }

    // usage: php index.php Cron/Platform fetch [platform] [since] [until]
    // usage: php index.php Cron/Platform fetch all [since] [until]
    public function fetch($platform = 'all', $since = null, $until = null)
    {
        $since = $since ?? date('Y-m-d', strtotime('-30 days'));
        $until = $until ?? date('Y-m-d');

        $platforms = ($platform === 'all') ? $this->platform_registry->enabled_names() : [$platform];

        foreach ($platforms as $name) {
            $this->run_fetch($name, $since, $until);
        }
    }

    // usage: php index.php Cron/Platform sync [platform] [since] [until]
    public function sync($platform = 'all', $since = null, $until = null)
    {
        $since = $since ?? date('Y-m-d', strtotime('-30 days'));
        $until = $until ?? date('Y-m-d');

        $platforms = ($platform === 'all') ? $this->platform_registry->enabled_names() : [$platform];

        foreach ($platforms as $name) {
            $this->run_sync($name, $since, $until);
        }
    }

    private function run_fetch($platform, $since, $until)
    {
        echo "[{$platform}] Fetch Contents {$since} to {$until}\n";

        $log_id = $this->Cron_log_model->start('fetch', $platform);

        try {
            // prepare driver
            $driver = $this->make_driver($platform);
            // prepare filter
            $filters = $this->build_filters($driver);
            // get contents
            $contents = $driver->fetch_contents($since, $until, $filters);

            if (empty($contents)) {
                echo "[{$platform}] No contents found.\n\n";
                $this->Cron_log_model->finish($log_id, 'success', 0);
                return;
            }

            foreach ($contents as &$row) {
                $row['platform'] = $platform;
            }

            // bulk upsert contents
            $result = $this->Ad_model->bulk_upsert_contents($contents);
            echo "[{$platform}] " . $result['created'] . " contents saved.\n\n";

            $this->Cron_log_model->finish($log_id, 'success', $result['created']);

        } catch (\Exception $e) {
            log_message('error', "[Cron/Platform::fetch_contents:{$platform}] " . $e->getMessage());
            echo "[{$platform}] ERROR: " . $e->getMessage() . "\n\n";

            $this->Cron_log_model->finish($log_id, 'failed', 0, $e->getMessage());
        }
    }

    private function run_sync($platform, $since, $until)
    {
        echo "[{$platform}] Sync Insights\n";

        $log_id = $this->Cron_log_model->start('sync', $platform);

        try {
            // prepare driver
            $driver = $this->make_driver($platform);
            // get saved content identifiers with campaign date ranges
            $saved = $this->Ad_model->get_identifiers_by_platform($platform);

            if (empty($saved)) {
                echo "[{$platform}] No active content found in DB.\n\n";
                $this->Cron_log_model->finish($log_id, 'success', 0);
                return;
            }

            // prepare content_id_map
            $content_id_map = array_column($saved, 'id', 'content_identifier');

            // fetch insights — GA4 groups by campaign date range, others call once
            if ($platform === 'ga4') {
                $insights = $this->fetch_ga4_insights_by_campaign($driver, $saved, $since, $until);
            } else {
                $identifiers = array_column($saved, 'content_identifier');
                $insights = $driver->fetch_insights($identifiers, $since, $until);
            }

            if (empty($insights)) {
                echo "[{$platform}] No insights returned from API.\n\n";
                $this->Cron_log_model->finish($log_id, 'success', 0);
                return;
            }

            // build metric rows
            $metric_rows = $this->build_metric_rows($insights, $content_id_map);
            // bulk upsert metrics
            $result = $this->Ad_model->bulk_upsert_metrics($metric_rows);
            echo "[{$platform}] " . $result['upserted'] . " metric rows saved.\n\n";

            $this->Cron_log_model->finish($log_id, 'success', $result['upserted']);

        } catch (\Exception $e) {
            log_message('error', "[Cron/Platform::sync_insights:{$platform}] " . $e->getMessage());
            echo "[{$platform}] ERROR: " . $e->getMessage() . "\n\n";

            $this->Cron_log_model->finish($log_id, 'failed', 0, $e->getMessage());
        }
    }

    private function fetch_ga4_insights_by_campaign($driver, $saved, $default_since, $default_until)
    {
        // group identifiers by unique campaign date range
        // articles with no campaign fall back to the default 1-month window
        $groups = [];
        foreach ($saved as $row) {
            $s = $row->campaign_start_date ?? $default_since;
            $u = $row->campaign_end_date   ?? $default_until;
            $key = $s . '|' . $u;
            $groups[$key][] = $row->content_identifier;
        }

        $all_insights = [];
        foreach ($groups as $key => $identifiers) {
            [$s, $u] = explode('|', $key);
            echo "[ga4] Syncing " . count($identifiers) . " article(s) for {$s} to {$u}\n";
            $group_insights = $driver->fetch_insights($identifiers, $s, $u);
            $all_insights = array_merge($all_insights, $group_insights);
        }

        return $all_insights;
    }

    private function make_driver($platform)
    {
        // get platform config
        $conf = $this->platform_registry->config($platform);

        if (!$conf) {
            throw new \RuntimeException("Unknown or disabled platform: {$platform}");
        }

        // include driver file
        require_once $conf['driver_path'];

        // instantiate driver
        $class = $conf['driver_class'];
        return new $class();
    }

    private function build_filters($driver)
    {
        $filters = [];

        // build keyword filter
        if ($driver->supports_keyword_filter()) {
            $kws = $this->Filter_keyword_model->get_by_type('keyword');
            $filters['keywords'] = array_column($kws, 'keyword');
        }

        // build hostname filter
        if ($driver->supports_hostname_filter()) {
            $kws = $this->Filter_keyword_model->get_by_type('hostname');
            $filters['hostnames'] = array_column($kws, 'keyword');
        }

        // build html filter
        if ($driver->supports_html_filter()) {
            $kws = $this->Filter_keyword_model->get_by_type('html');
            $filters['html'] = array_column($kws, 'keyword');
        }

        return $filters;
    }

    private function build_metric_rows($insights, $content_map)
    {
        $rows = [];
        foreach ($insights as $identifier => $metrics) {
            if (!isset($content_map[$identifier]))
                continue;

            $ad_content_id = $content_map[$identifier];

            foreach ($metrics as $metric_name => $metric_value) {
                if (is_array($metric_value) || is_object($metric_value)) {
                    $metric_value = json_encode($metric_value);
                }

                $rows[] = [
                    'ad_content_id' => $ad_content_id,
                    'metric_name' => $metric_name,
                    'metric_value' => $metric_value,
                ];
            }
        }

        return $rows;
    }
}
