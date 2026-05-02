<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ga4 extends CI_Controller
{
    public function __construct()
    {
        // ensure only CLI access
        if (!is_cli()) {
            show_error('This controller can only be accessed via CLI');
        }

        parent::__construct();
        // load required models and libraries
        $this->load->model('Filter_keyword_model');
        $this->load->model('Ad_content_model');
        $this->load->library('ga4_api');
    }

    /**
     * fetch GA4 articles and insert to database.
     * usage: php index.php Cron/Ga4 fetch_articles [since] [until]
     */
    public function fetch_articles($since = null, $until = null)
    {
        $since = $since ?? date('Y-m-d', strtotime('-30 days'));
        $until = $until ?? date('Y-m-d');

        echo "[GA4] Fetch Articles {$since} to {$until}\n\n";

        try {
            // get keywords filter from database
            $keywords = $this->Filter_keyword_model->get_all();

            // set filter
            $html_filters = [];
            $hostname_filters = [];
            if (!empty($keywords)) {
                foreach ($keywords as $kw) {
                    if ($kw->type === 'html') {
                        $html_filters[] = $kw->keyword;
                    } elseif ($kw->type === 'hostname') {
                        $hostname_filters[] = $kw->keyword;
                    }
                }
            }

            // fetch all articles within date range, applying the html filters
            $data = $this->ga4_api->get_articles($since, $until, $hostname_filters, $html_filters);

            if (empty($data['ad_contents'])) {
                echo "[GA4] No articles found.\n";
                return;
            }

            // prepare upsert contents
            $content_rows = [];
            foreach ($data['ad_contents'] as $item) {
                $content_rows[] = [
                    'title' => substr($item['page_title'] ?? '', 0, 200),
                    'platform' => 'ga4',
                    'content_identifier' => $item['page_path'],
                    'ad_type' => 'article',
                ];
            }

            // upsert contents
            $this->Ad_content_model->bulk_upsert_contents($content_rows);
            echo "[GA4] Contents upserted: " . count($content_rows) . "\n";

        } catch (\Exception $e) {
            log_message('error', '[Cron/Ga4::fetch_articles] ' . $e->getMessage());
            echo "[GA4] ERROR: " . $e->getMessage() . "\n";
        }

        echo "[GA4] Fetch Articles Done.\n";
    }

    /**
     * sync GA4 metrics for existing content.
     * usage: php index.php Cron/Ga4 sync_insights [since] [until]
     */
    public function sync_insights($since = null, $until = null)
    {
        $since = $since ?? date('Y-m-d', strtotime('-30 days'));
        $until = $until ?? date('Y-m-d');

        echo "[GA4] Sync Insights {$since} to {$until}\n\n";

        try {
            // get identifiers contents
            $saved = $this->Ad_content_model->get_identifiers_by_platform('ga4', $since, $until);
            $urls = array_column($saved, 'content_identifier');
            $content_map = array_column($saved, 'id', 'content_identifier');

            if (empty($urls)) {
                echo "[GA4] No active contents found in database.\n";
                return;
            }

            // fetch metrics from ga4 using specific urls
            $data = $this->ga4_api->sync_articles_insight($since, $until, $urls);

            if (empty($data['ad_metrics'])) {
                echo "[GA4] No metrics found from GA4 API.\n";
                return;
            }

            // build metric rows
            $metric_rows = [];
            foreach ($data['ad_metrics'] as $item) {
                $ad_content_id = $content_map[$item['page_path']] ?? null;
                if (!$ad_content_id) continue;

                foreach ($item['metrics'] as $metric_name => $metric_value) {
                    $metric_rows[] = [
                        'ad_content_id' => $ad_content_id,
                        'metric_name'   => $metric_name,
                        'metric_value'  => $metric_value,
                    ];
                }
            }

            // upsert metrics
            $result = $this->Ad_content_model->bulk_upsert_metrics($metric_rows);
            echo "[GA4] Metrics upserted: {$result['upserted']}\n";

        } catch (\Exception $e) {
            log_message('error', '[Cron/Ga4::sync_insights] ' . $e->getMessage());
            echo "[GA4] ERROR: " . $e->getMessage() . "\n";
        }

        echo "[GA4] Sync Insights Done.\n";
    }
}
