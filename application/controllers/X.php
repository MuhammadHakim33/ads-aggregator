<?php
defined('BASEPATH') or exit('No direct script access allowed');

class X extends CI_Controller 
{
    public function __construct()
    {
        parent::__construct();
        if (!is_cli()) {
            echo "Access denied. This controller can only be run via CLI.\n";
            exit;
        }

        $this->load->library('meta_graph');
        $this->load->library('ga4');
        $this->load->library('youtube');
        $this->load->model('Filter_keyword_model');
        $this->load->model('Ad_content_model');
        $this->load->model('Client_identifier_model');
    }

    // /**
    //  * sync Facebook posts & insights.
    //  * usage: php index.php cron sync_facebook [since] [until]
    //  */
    // public function sync_facebook($since = null, $until = null)
    // {
    //     $since = $since ?? date('Y-m-d', strtotime('-30 days'));
    //     $until = $until ?? date('Y-m-d');

    //     echo "[Facebook] Sync {$since} to {$until}\n\n";

    //     try {
    //         // get keywords from database
    //         $keywords = $this->Filter_keyword_model->get_all('facebook');

    //         // get facebook posts from meta graph api
    //         $all_posts = $this->fetch_with_keywords('get_facebook_posts', $since, $until, $keywords);

    //         if (empty($all_posts)) {
    //             echo "[Facebook] No posts found.\n";
    //             return;
    //         }

    //         // set data for upsert contents
    //         $content_rows = [];
    //         // $skipped = 0;
    //         foreach ($all_posts as $post) {
    //             // extract identifier
    //             // $identifier = $this->extract_identifier($post, $keywords);
                
    //             // find client_id from identifier
    //             // $client_id  = $identifier
    //             //     ? $this->Client_identifier_model->find_client_by_identifier('facebook', $identifier)
    //             //     : null;

    //             // if (!$client_id) {
    //             //     echo "[Facebook] Skip post {$post['id']}: no client match for '{$identifier}'\n";
    //             //     $skipped++;
    //             //     continue;
    //             // }

    //             $content_rows[] = [
    //                 // 'client_id'=> $client_id,
    //                 'title' => substr($post['message'], 0, 200),
    //                 'platform' => 'facebook',
    //                 'content_identifier' => $post['id'],
    //                 'ad_type' => 'social',
    //             ];
    //         }

    //         // if ($skipped > 0) {
    //         //     echo "[Facebook] Skipped {$skipped} post(s) — client not found.\n";
    //         // }

    //         // upsert contents
    //         $this->Ad_content_model->bulk_upsert_contents($content_rows);
    //         echo "[Facebook] Contents upserted: " . count($content_rows) . "\n";

    //         // get content's ids by platform
    //         $content_ids = $this->Ad_content_model->get_identifiers_by_platform('facebook', $since, $until);
    //         $content_id_map = array_column($content_ids, 'id', 'content_identifier');

    //         // fetch & upsert metrics
    //         $insights = $this->meta_graph->get_facebook_post_insights(array_keys($content_id_map));
    //         $metric_rows = $this->build_metric_rows($insights, $content_id_map);

    //         // upsert metrics
    //         $result = $this->Ad_content_model->bulk_upsert_metrics($metric_rows);
    //         echo "[Facebook] Metrics upserted: {$result['upserted']}\n";

    //     } catch (\Exception $e) {
    //         log_message('error', '[Cron::sync_facebook] ' . $e->getMessage());
    //         echo "[Facebook] ERROR: " . $e->getMessage() . "\n";
    //     }

    //     echo "[Facebook] Done.\n";
    // }

    // /**
    //  * sync Instagram media & insights.
    //  * usage: php index.php cron sync_instagram [since] [until]
    //  */
    // public function sync_instagram($since = null, $until = null)
    // {
    //     $since = $since ?? date('Y-m-d', strtotime('-30 days'));
    //     $until = $until ?? date('Y-m-d');

    //     echo "[Instagram] Sync {$since} to {$until}\n";

    //     try {
    //         // get keywords from database
    //         $keywords  = $this->Filter_keyword_model->get_all('instagram');
            
    //         // get instagram posts from meta graph api
    //         $all_posts = $this->fetch_with_keywords('get_instagram_media', $since, $until, $keywords);

    //         if (empty($all_posts)) {
    //             echo "[Instagram] No posts found.\n";
    //             return;
    //         }

    //         // prepare upsert contents — resolve client_id per post
    //         $content_rows = [];
    //         // $skipped = 0;
    //         foreach ($all_posts as $post) {
    //             // $identifier = $this->extract_identifier($post, $keywords);
    //             // $client_id  = $identifier
    //             //     ? $this->Client_identifier_model->find_client_by_identifier('instagram', $identifier)
    //             //     : null;

    //             // if (!$client_id) {
    //             //     echo "[Instagram] Skip post {$post['id']}: no client match for '{$identifier}'\n";
    //             //     $skipped++;
    //             //     continue;
    //             // }

    //             $content_rows[] = [
    //                 // 'client_id'          => $client_id,
    //                 'title' => substr($post['caption'], 0, 200),
    //                 'platform' => 'instagram',
    //                 'content_identifier' => $post['id'],
    //                 'ad_type' => 'social',
    //             ];
    //         }

    //         // if ($skipped > 0) {
    //         //     echo "[Instagram] Skipped {$skipped} post(s) — client not found.\n";
    //         // }

    //         // upsert contents
    //         $this->Ad_content_model->bulk_upsert_contents($content_rows);
    //         echo "[Instagram] Contents upserted: " . count($content_rows) . "\n";

    //         // get content ids
    //         $content_ids = $this->Ad_content_model->get_identifiers_by_platform('instagram', $since, $until);
    //         $content_id_map = array_column($content_ids, 'id', 'content_identifier');

    //         // fetch & upsert metrics
    //         $insights = $this->meta_graph->get_instagram_media_insights(array_keys($content_id_map));
    //         $metric_rows = $this->build_metric_rows($insights, $content_id_map);

    //         // upsert metrics
    //         $result = $this->Ad_content_model->bulk_upsert_metrics($metric_rows);
    //         echo "[Instagram] Metrics upserted: {$result['upserted']}\n";

    //     } catch (\Exception $e) {
    //         log_message('error', '[Cron::sync_instagram] ' . $e->getMessage());
    //         echo "[Instagram] ERROR: " . $e->getMessage() . "\n";
    //     }

    //     echo "[Instagram] Done.\n";
    // }

    // /**
    //  * Sync GA4 articles & metrics.
    //  * Usage: php index.php cron sync_ga4 [since] [until]
    //  */
    // public function sync_ga4($since = null, $until = null)
    // {
    //     $since = $since ?? date('Y-m-d', strtotime('-30 days'));
    //     $until = $until ?? date('Y-m-d');
    // 
    //     echo "[GA4] Sync {$since} to {$until}\n\n";
    // 
    //     try {
    //         // get identifiers active contents
    //         // we fetch all active ga4 contents to sync their metrics for the given date range
    //         $saved = $this->Ad_content_model->get_identifiers_active_contents(1, 'ga4');
    //         $urls = array_column($saved, 'content_identifier');
    //         $content_map = array_column($saved, 'id', 'content_identifier');
    // 
    //         if (empty($urls)) {
    //             echo "[GA4] No active contents found in database.\n";
    //             return;
    //         }
    // 
    //         // fetch metrics from ga4 using specific urls
    //         $data = $this->ga4->sync_articles_insight($since, $until, $urls);
    // 
    //         if (empty($data['ad_metrics'])) {
    //             echo "[GA4] No metrics found from GA4 API.\n";
    //             return;
    //         }
    // 
    //         // build metric rows
    //         $metric_rows = [];
    //         foreach ($data['ad_metrics'] as $item) {
    //             $ad_content_id = $content_map[$item['page_path']] ?? null;
    //             if (!$ad_content_id) continue;
    // 
    //             foreach ($item['metrics'] as $metric_name => $metric_value) {
    //                 $metric_rows[] = [
    //                     'ad_content_id' => $ad_content_id,
    //                     'metric_name'   => $metric_name,
    //                     'metric_value'  => $metric_value,
    //                 ];
    //             }
    //         }
    // 
    //         // upsert metrics
    //         $result = $this->Ad_content_model->bulk_upsert_metrics($metric_rows);
    //         echo "[GA4] Metrics upserted: {$result['upserted']}\n";
    // 
    //     } catch (\Exception $e) {
    //         log_message('error', '[Cron::sync_ga4] ' . $e->getMessage());
    //         echo "[GA4] ERROR: " . $e->getMessage() . "\n";
    //     }
    // 
    //     echo "[GA4] Done.\n";
    // }

    public function test()
    {
        $since = date('Y-m-d', strtotime('-30 days'));
        $until = date('Y-m-d');
        
        $videos = $this->youtube->get_videos($since, $until);
        // $video_ids = array_column($videos, 'video_id');
        // $video_insights = $this->youtube->get_video_insights($video_ids);

        // print_r($videos);
        // print_r($video_insights);

        $keywords = ['Content'];
        if (!empty($keywords)) {
            foreach ($videos as $index => $video) {
                $description = $video['description'] ?? '';
                
                foreach ($keywords as $kw) {
                    echo (stripos($description, $kw) === FALSE) . "\n";
                    // if keyword is found, continue to next video
                    // if (stripos($description, $kw) === TRUE) {
                    //     break;
                    // }

                    // // if keyword not found, unset video
                    // unset($videos[$index]);
                }
            }
        }

        print_r($videos);
    }

    // /**
    //  * Sync YouTube videos & metrics.
    //  * Usage: php index.php cron sync_youtube [since] [until]
    //  */
    // public function sync_youtube($since = null, $until = null)
    // {
    //     $since = $since ?? date('Y-m-d', strtotime('-30 days'));
    //     $until = $until ?? date('Y-m-d');
    // 
    //     echo "[YouTube] Sync {$since} to {$until}\n\n";
    // 
    //     try {
    //         $keywords = $this->Filter_keyword_model->get_all('yt');
    //         
    //         $published_after  = date('Y-m-d\T00:00:00\Z', strtotime($since));
    //         $published_before = date('Y-m-d\T23:59:59\Z', strtotime($until));
    // 
    //         $videos = $this->youtube->get_videos($published_after, $published_before);
    // 
    //         // filter videos if keywords are provided
    //         $all_posts = [];
    //         if (empty($keywords)) {
    //             $all_posts = $videos;
    //         } else {
    //             foreach ($videos as $video) {
    //                 $description = $video['snippet']['description'] ?? '';
    //                 foreach ($keywords as $kw) {
    //                     if (stripos($description, $kw->keyword) !== FALSE) {
    //                         $all_posts[] = $video;
    //                         break; // matched one keyword, no need to check others for this video
    //                     }
    //                 }
    //             }
    //         }
    // 
    //         if (empty($all_posts)) {
    //             echo "[YouTube] No videos found.\n";
    //             return;
    //         }
    // 
    //         // prepare upsert contents — resolve client_id per post
    //         $content_rows = [];
    //         $skipped      = 0;
    //         foreach ($all_posts as $post) {
    //             $identifier = $this->extract_identifier($post, $keywords);
    //             // $client_id  = $identifier
    //             //     ? $this->Client_identifier_model->find_client_by_identifier('yt', $identifier)
    //             //     : null;
    // 
    //             // if (!$client_id) {
    //             //     echo "[YouTube] Skip video {$post['id']}: no client match for '{$identifier}'\n";
    //             //     $skipped++;
    //             //     continue;
    //             // }
    // 
    //             $content_rows[] = [
    //                 'client_id'          => 1,
    //                 'platform'           => 'yt',
    //                 'content_identifier' => $post['id'],
    //                 'ad_type'            => 'video',
    //             ];
    //         }
    // 
    //         if ($skipped > 0) {
    //             echo "[YouTube] Skipped {$skipped} video(s) — client not found.\n";
    //         }
    // 
    //         // upsert contents
    //         $this->Ad_content_model->bulk_upsert_contents($content_rows);
    //         echo "[YouTube] Contents upserted: " . count($content_rows) . "\n";
    // 
    //         // get identifiers (all clients)
    //         $saved       = $this->Ad_content_model->get_identifiers_by_platform('yt');
    //         $content_map = array_column($saved, 'id', 'content_identifier');
    // 
    //         // build metric rows from statistics
    //         $metric_rows = [];
    //         foreach ($all_posts as $post) {
    //             $video_id = $post['id'];
    //             if (!isset($content_map[$video_id])) continue;
    // 
    //             $ad_content_id = $content_map[$video_id];
    //             $metrics = $post['statistics'] ?? [];
    // 
    //             foreach ($metrics as $metric_name => $metric_value) {
    //                 $metric_rows[] = [
    //                     'ad_content_id' => $ad_content_id,
    //                     'metric_name'   => $metric_name,
    //                     'metric_value'  => $metric_value,
    //                 ];
    //             }
    //         }
    // 
    //         // upsert metrics
    //         $result = $this->Ad_content_model->bulk_upsert_metrics($metric_rows);
    //         echo "[YouTube] Metrics upserted: {$result['upserted']}\n";
    // 
    //     } catch (\Exception $e) {
    //         log_message('error', '[Cron::sync_youtube] ' . $e->getMessage());
    //         echo "[YouTube] ERROR: " . $e->getMessage() . "\n";
    //     }
    // 
    //     echo "[YouTube] Done.\n";
    // }
    // 
    // private function extract_identifier($post, $keywords)
    // {
    //     $text = $post['caption'] ?? $post['message'] ?? $post['snippet']['description'] ?? '';
    // 
    //     foreach ($keywords as $kw) {
    //         $pattern = '/' . preg_quote($kw->keyword, '/') . '\s+([\w.]+)/iu';
    //         if (preg_match($pattern, $text, $matches)) {
    //             return $matches[1]; // e.g. "bankbri_id"
    //         }
    //     }
    // 
    //     return null;
    // }

    private function fetch_with_keywords($method, $since, $until, $keywords)
    {
        // if no keywords, get all posts
        if (empty($keywords)) {
            return $this->meta_graph->$method($since, $until);
        }

        // if keywords exists, get posts filtered by keywords
        $all = [];
        foreach ($keywords as $kw) {
            $posts = $this->meta_graph->$method($since, $until, $kw->keyword);
            $all = array_merge($all, $posts);
        }

        return $all;
    }

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
                    'metric_name'   => $metric_name,
                    'metric_value'  => $metric_value,
                ];
            }
        }

        return $rows;
    }
}