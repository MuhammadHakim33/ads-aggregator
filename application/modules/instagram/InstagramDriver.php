<?php
require_once APPPATH . 'modules/meta/MetaPlatformDriver.php';
require_once APPPATH . 'exceptions/PartialSuccessException.php';

class InstagramDriver extends MetaPlatformDriver
{
    protected $reels_metrics = [];
    protected $fields;
    protected $insight_fields;

    public function __construct()
    {
        parent::__construct();
        $this->CI->config->load('platforms');

        $cred = $this->CI->Platform_credential_model->get_by_platform('meta');
        if (empty($cred) || empty($cred['ig_account_id'])) {
            throw new \Exception("credentials for instagram are empty or incomplete.");
        }

        $this->credentials = $cred;
        $platform_config = $this->CI->config->item('platforms')['instagram'] ?? [];
        $this->metrics = $platform_config['metrics'] ?? [];
        $this->reels_metrics = $platform_config['reels_metrics'] ?? [];
        $this->fields = $platform_config['fields'] ?? '';
        $this->insight_fields = $platform_config['insight_fields'] ?? '';
    }

    public function name()
    {
        return 'instagram';
    }

    public function fetch_contents($since, $until, $filters = [])
    {
        $ig_id = $this->credentials['ig_account_id'] ?? '';
        $keywords = $filters['keywords'] ?? [];

        $params = [
            'fields' => $this->fields,
            'since' => strtotime($since . ' 00:00:00'),
            'until' => strtotime($until . ' 23:59:59'),
            'limit' => 100
        ];

        // request instagram media from meta graph api
        $response = $this->make_request('get', $ig_id . '/media', $params);
        $raw = $response['data'] ?? [];

        log_message('info', "[InstagramDriver::fetch_contents] " . json_encode($response));

        // handle pagination to get all media
        while (!empty($response['paging']['next'])) {
            $next_url = $response['paging']['next'];
            $response = $this->CI->request->get($next_url);

            log_message('info', "[InstagramDriver::fetch_contents] " . json_encode($response));

            if (!empty($response['data'])) {
                $raw = array_merge($raw, $response['data']);
            } else {
                break;
            }
        }

        // filter posts based on keyword
        if (!empty($keywords)) {
            $raw = array_filter($raw, function ($p) use ($keywords) {
                foreach ($keywords as $kw) {
                    if (stripos($p['caption'] ?? '', $kw) !== false)
                        return true;
                }
                return false;
            });
            $raw = array_values($raw);
        }

        // map to ad_contents database schema
        return array_map(function ($p) {
            // thumbnail_url is available for VIDEO/REELS, media_url for IMAGE posts
            // Both are CDN URLs from Meta Graph API (may expire, refreshed on next cron run)
            $thumbnail = $p['thumbnail_url'] ?? $p['media_url'] ?? null;

            return [
                'title' => mb_substr($p['caption'] ?? 'no caption', 0, 200),
                'content_identifier' => $p['id'],
                'published_at' => isset($p['timestamp']) ? date('Y-m-d H:i:s', strtotime($p['timestamp'])) : null,
                'source' => $p['permalink'] ?? null,
                'platform' => 'instagram',
                'thumbnail' => $thumbnail,
            ];
        }, $raw);
    }

    public function fetch_insights($identifiers)
    {
        $insights = [];
        $errors = [];

        $standard_metrics = implode(',', $this->metrics);

        $batch = [];
        foreach ($identifiers as $id) {
            $batch[] = [
                'method' => 'GET',
                'relative_url' => "{$id}?fields={$this->insight_fields},insights.metric({$standard_metrics})",
            ];
        }

        log_message('info', "[InstagramDriver::fetch_insights::batch_payload] " . json_encode($batch));

        // request instagram media details and insights from meta graph api
        $response = $this->make_request('post', '', ['batch' => json_encode($batch)]);

        log_message('info', "[InstagramDriver::fetch_insights::response] " . json_encode($response));

        $reels_ids = [];
        // normalize standard insights and identify reels posts
        foreach ($response as $index => $res) {
            $id = $identifiers[$index];
            if ($res['code'] === 200) {
                $body = json_decode($res['body'], TRUE);

                // save standard insights
                $insights[$id] = $this->normalize_insights($body['insights']['data'] ?? []);

                // calculate engagement rate
                $interactions = $insights[$id]['total_interactions'] ?? 0;
                $reach = $insights[$id]['reach'] ?? 0;
                $insights[$id]['engagement_rate'] = ($reach > 0) ? round(($interactions / $reach) * 100, 2) : 0;

                // check if the media is reels
                $product_type = $body['media_product_type'] ?? '';
                if ($product_type === 'REELS') {
                    $reels_ids[] = $id;
                }
                continue;
            }

            // collect error messages per media
            $body = json_decode($res['body'], true);
            $msg = $body['error']['message'] ?? $res['body'];
            $errors[] = "[media {$id}] {$msg}";
            $time = date('Y-m-d H:i:s');
            log_message('error', "[{$time}] instagram api media insights error [{$id}]: {$msg}");
        }

        // fetch reels specific insights safely only for reels media
        if (!empty($reels_ids)) {
            $reels_metrics_str = implode(',', $this->reels_metrics);
            $reels_batch = [];
            foreach ($reels_ids as $id) {
                $reels_batch[] = [
                    'method' => 'GET',
                    'relative_url' => "{$id}/insights?metric={$reels_metrics_str}",
                ];
            }

            $reels_response = $this->make_request('post', '', ['batch' => json_encode($reels_batch)]);

            foreach ($reels_response as $index => $res) {
                $id = $reels_ids[$index];
                if ($res['code'] === 200) {
                    $body = json_decode($res['body'], TRUE);
                    $reels_insights = $this->normalize_insights($body['data'] ?? []);

                    // merge standard insights with reels specific insights
                    $insights[$id] = array_merge($insights[$id], $reels_insights);
                    continue;
                }

                // collect error messages per reels
                $body = json_decode($res['body'], true);
                $msg = $body['error']['message'] ?? $res['body'];
                $errors[] = "[reels {$id}] {$msg}";
                $time = date('Y-m-d H:i:s');
                log_message('error', "[{$time}] instagram reels api insights error [{$id}]: {$msg}");
            }
        }

        // throw exception if partial errors occurred
        if (!empty($errors)) {
            throw new PartialSuccessException($errors, $insights);
        }

        return $insights;
    }
}
