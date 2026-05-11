<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Facebook extends CI_Controller
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
        $this->load->library('meta_graph');
    }

    /**
     * fetch facebook posts and insert to database.
     * usage: php index.php Cron/Facebook fetch_posts [since] [until]
     */
    public function fetch_posts($since = null, $until = null)
    {
        $since = $since ?? date('Y-m-d', strtotime('-30 days'));
        $until = $until ?? date('Y-m-d');

        echo "[Facebook] Fetch Posts {$since} to {$until}\n\n";

        try {
            // get keywords from database
            $keywords = $this->Filter_keyword_model->get_by_type('keyword');
            $keyword_strings = array_map(function($k) { return $k->keyword; }, $keywords);
            
            // fetch posts using meta_graph
            $posts = $this->meta_graph->get_facebook_posts($since, $until, $keyword_strings);

            if (empty($posts)) {
                echo "[Facebook] No posts found.\n";
                return;
            }

            // prepare upsert contents
            $content_rows = [];
            foreach ($posts as $post) {
                $content_rows[] = [
                    'title' => substr($post['message'] ?? '', 0, 200),
                    'platform' => 'facebook',
                    'content_identifier' => $post['id'],
                    'ad_type' => 'social',
                ];
            }

            // upsert contents
            $this->Ad_content_model->bulk_upsert_contents($content_rows);

        } catch (\Exception $e) {
            log_message('error', '[Cron/Facebook::fetch_posts] ' . $e->getMessage());
            echo "[Facebook] ERROR: " . $e->getMessage() . "\n";
        }

        echo "[Facebook] Fetch Posts Done.\n";
    }

    /**
     * sync facebook post insights for existing content.
     * usage: php index.php Cron/Facebook sync_insights [since] [until]
     */
    public function sync_insights($since = null, $until = null)
    {
        $since = $since ?? date('Y-m-d', strtotime('-30 days'));
        $until = $until ?? date('Y-m-d');

        echo "[Facebook] Sync Insights {$since} to {$until}\n\n";

        try {
            // get content ids
            $content_ids = $this->Ad_content_model->get_identifiers_by_platform('facebook', $since, $until);
            
            if (empty($content_ids)) {
                echo "[Facebook] No active content found in the given date range.\n";
                return;
            }

            $content_id_map = array_column($content_ids, 'id', 'content_identifier');

            // fetch insights using meta_graph
            $insights = $this->meta_graph->get_facebook_post_insights(array_keys($content_id_map));
            
            if (empty($insights)) {
                echo "[Facebook] No insights returned from API.\n";
                return;
            }

            $metric_rows = $this->build_metric_rows($insights, $content_id_map);

            // upsert metrics
            $this->Ad_content_model->bulk_upsert_metrics($metric_rows);

        } catch (\Exception $e) {
            log_message('error', '[Cron/Facebook::sync_insights] ' . $e->getMessage());
            echo "[Facebook] ERROR: " . $e->getMessage() . "\n";
        }

        echo "[Facebook] Sync Insights Done.\n";
    }


    // format response data to database metric rows
    private function build_metric_rows($insights, $content_map)
    {
        $rows = [];
        foreach ($insights as $post_id => $metrics) {
            if (!isset($content_map[$post_id])) continue;

            $ad_content_id = $content_map[$post_id];

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
