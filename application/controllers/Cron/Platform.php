<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . 'exceptions/PartialSuccessException.php';

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
        $log_id = $this->Cron_log_model->start('fetch', $platform);

        try {
            // prepare driver
            $conf = $this->platform_registry->config($platform);
            $driver = $this->make_driver($platform);
            // prepare filter
            $filters = $this->build_filters($conf, $platform);
            // get contents
            $contents = $driver->fetch_contents($since, $until, $filters);

            if (empty($contents)) {
                $this->Cron_log_model->finish($log_id, 'success', 0);
                return;
            }

            foreach ($contents as &$row) {
                $row['platform'] = $platform;
            }

            // bulk upsert contents
            $result = $this->Ad_model->bulk_upsert_contents($contents);

            $this->Cron_log_model->finish($log_id, 'success', $result['created']);

        } catch (PartialSuccessException $e) {
            // save the successful data and record the error
            $partial_contents = $e->getPartialData();
            foreach ($partial_contents as &$row) {
                $row['platform'] = $platform;
            }
            $saved = empty($partial_contents) ? ['created' => 0] : $this->Ad_model->bulk_upsert_contents($partial_contents);
            $status = empty($partial_contents) ? 'failed' : 'partial';

            $time = date('Y-m-d H:i:s');
            log_message('error', "[{$time}] [Cron/Platform::fetch_contents:{$platform}] (partial) " . $e->getMessage());
            $this->Cron_log_model->finish($log_id, $status, $saved['created'], $e->getMessage());

        } catch (\Throwable $e) {
            // fatal error no data successfully inserted
            $time = date('Y-m-d H:i:s');
            log_message('error', "[{$time}] [Cron/Platform::fetch_contents:{$platform}] " . $e->getMessage());
            $this->Cron_log_model->finish($log_id, 'failed', 0, $e->getMessage());
        }
    }

    private function run_sync($platform, $since, $until)
    {
        $log_id = $this->Cron_log_model->start('sync', $platform);

        try {
            // prepare driver
            $driver = $this->make_driver($platform);
            // get saved content identifiers with campaign date ranges
            $saved = $this->Ad_model->get_identifiers_by_platform($platform);

            if (empty($saved)) {
                $this->Cron_log_model->finish($log_id, 'success', 0);
                return;
            }

            // prepare content_id_map
            $content_id_map = array_column($saved, 'id', 'content_identifier');

            // fetch insights catch PartialSuccessException separately
            $insights = [];
            $error_message = null;

            try {
                if ($platform === 'ga4') {
                    $insights = $this->fetch_ga4_insights_by_campaign($driver, $saved, $since, $until);
                } else {
                    $identifiers = array_column($saved, 'content_identifier');
                    $insights = $driver->fetch_insights($identifiers, $since, $until);
                }
            } catch (PartialSuccessException $e) {
                // save the partial data and record the error message
                $insights = $e->getPartialData();
                $error_message = $e->getMessage();
                $time = date('Y-m-d H:i:s');
                log_message('error', "[{$time}] [Cron/Platform::sync_insights:{$platform}] (partial) {$error_message}");
            }

            if (empty($insights)) {
                $status = $error_message ? 'failed' : 'success';
                $this->Cron_log_model->finish($log_id, $status, 0, $error_message);
                return;
            }

            // build metric rows dari insights yang berhasil
            $metric_rows = $this->build_metric_rows($insights, $content_id_map);
            // bulk upsert metrics — hanya data yang valid masuk DB
            $result = $this->Ad_model->bulk_upsert_metrics($metric_rows);

            // if there is an error, mark as partial, otherwise success
            $status = $error_message ? 'partial' : 'success';
            $this->Cron_log_model->finish($log_id, $status, $result['upserted'], $error_message);

        } catch (\Throwable $e) {
            // fatal error no data successfully inserted for this platform
            $time = date('Y-m-d H:i:s');
            log_message('error', "[{$time}] [Cron/Platform::sync_insights:{$platform}] " . $e->getMessage());
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
            $u = $row->campaign_end_date ?? $default_until;
            $key = $s . '|' . $u;
            $groups[$key][] = $row->content_identifier;
        }

        $all_insights = [];
        foreach ($groups as $key => $identifiers) {
            [$s, $u] = explode('|', $key);
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

    private function build_filters($conf, $platform)
    {
        $filters = [];
        $kw_platform = in_array($platform, ['facebook', 'instagram']) ? 'meta' : $platform;

        // build keyword filter
        if (!empty($conf['filters']['keyword'])) {
            $kws = $this->Filter_keyword_model->get_by_type('keyword', $kw_platform);
            $filters['keywords'] = array_column($kws, 'keyword');
        }

        // build hostname filter
        if (!empty($conf['filters']['hostname'])) {
            $kws = $this->Filter_keyword_model->get_by_type('hostname', $kw_platform);
            $filters['hostnames'] = array_column($kws, 'keyword');
        }

        // build html filter
        if (!empty($conf['filters']['html'])) {
            $kws = $this->Filter_keyword_model->get_by_type('html', $kw_platform);
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
