<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Youtube extends CI_Controller
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
        $this->load->model('Ad_model');
        $this->load->library('youtube_api');
    }

    /**
     * fetch youtube videos and insert to database.
     * usage: php index.php Cron/Youtube fetch_videos [since] [until]
     */
    public function fetch_videos($since = null, $until = null)
    {
        $since = $since ?? date('Y-m-d', strtotime('-30 days'));
        $until = $until ?? date('Y-m-d');

        echo "[YouTube] Fetch Videos {$since} to {$until}\n\n";

        try {
            // get keywords from database
            $keywords = $this->Filter_keyword_model->get_by_type('keyword');
            $keyword_strings = array_map(function($k) { return $k->keyword; }, $keywords);
            
            // fetch youtube videos
            $videos = $this->youtube_api->get_videos($since, $until, $keyword_strings);

            if (empty($videos)) {
                echo "[YouTube] No videos found.\n";
                return;
            }

            // prepare upsert contents
            $content_rows = [];
            foreach ($videos as $post) {
                $content_rows[] = [
                    'title' => substr($post['title'] ?? '', 0, 200),
                    'platform' => 'yt',
                    'content_identifier' => $post['video_id'],
                    'ad_type' => 'video',
                ];
            }

            // upsert contents
            $this->Ad_model->bulk_upsert_contents($content_rows);

        } catch (\Exception $e) {
            log_message('error', '[Cron/Youtube::fetch_videos] ' . $e->getMessage());
            echo "[YouTube] ERROR: " . $e->getMessage() . "\n";
        }

        echo "[YouTube] Fetch Videos Done.\n";
    }

    /**
     * sync youtube video insights for existing content.
     * usage: php index.php Cron/Youtube sync_insights [since] [until]
     */
    public function sync_insights($since = null, $until = null)
    {
        $since = $since ?? date('Y-m-d', strtotime('-30 days'));
        $until = $until ?? date('Y-m-d');

        echo "[YouTube] Sync Insights {$since} to {$until}\n\n";

        try {
            // get content ids
            $content_ids = $this->Ad_model->get_identifiers_by_platform('yt', $since, $until);
            
            if (empty($content_ids)) {
                echo "[YouTube] No active content found in the given date range.\n";
                return;
            }

            $content_id_map = array_column($content_ids, 'id', 'content_identifier');

            // fetch insights using youtube library
            $insights = $this->youtube_api->get_video_insights(array_keys($content_id_map));
            
            if (empty($insights)) {
                echo "[YouTube] No insights returned from API.\n";
                return;
            }

            $metric_rows = $this->build_metric_rows($insights, $content_id_map);

            // upsert metrics
            $this->Ad_model->bulk_upsert_metrics($metric_rows);

        } catch (\Exception $e) {
            log_message('error', '[Cron/Youtube::sync_insights] ' . $e->getMessage());
            echo "[YouTube] ERROR: " . $e->getMessage() . "\n";
        }

        echo "[YouTube] Sync Insights Done.\n";
    }

    // format response data to database metric rows
    private function build_metric_rows($insights, $content_map)
    {
        $rows = [];
        foreach ($insights as $video_id => $metrics) {
            if (!isset($content_map[$video_id])) continue;

            $ad_content_id = $content_map[$video_id];

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
