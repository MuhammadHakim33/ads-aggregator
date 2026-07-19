<?php
require_once APPPATH . 'modules/meta/MetaPlatformDriver.php';
require_once APPPATH . 'exceptions/PartialSuccessException.php';

class FacebookDriver extends MetaPlatformDriver
{
    private $pat;
    protected $fields;
    protected $insight_fields;

    public function __construct()
    {
        parent::__construct();
        $this->CI->config->load('platforms');

        $credential = $this->CI->Platform_credential_model->get_by_platform('meta');
        if (empty($credential) || empty($credential['fb_page_id'])) {
            throw new \Exception("credentials for facebook are empty or incomplete.");
        }

        $this->credentials = $credential;
        $platform_config = $this->CI->config->item('platforms')['facebook'] ?? [];
        $this->metrics = $platform_config['metrics'] ?? [];
        $this->fields = $platform_config['fields'] ?? '';
        $this->insight_fields = $platform_config['insight_fields'] ?? '';
    }

    public function name()
    {
        return 'facebook';
    }

    private function get_page_access_token()
    {
        if (!empty($this->pat)) {
            return $this->pat;
        }

        $page_id = $this->credentials['fb_page_id'] ?? '';

        // get page access token from meta graph api
        $response = $this->make_request('get', $page_id, ['fields' => 'access_token']);

        if (empty($response['access_token'])) {
            throw new \RuntimeException('failed getting page access token');
        }

        return $this->pat = $response['access_token'];
    }

    public function fetch_contents($since, $until, $filters = [])
    {
        $page_id = $this->credentials['fb_page_id'] ?? '';
        $pat = $this->get_page_access_token();
        $keywords = $filters['keywords'] ?? [];

        $params = [
            'fields' => $this->fields,
            'since' => strtotime($since . ' 00:00:00'),
            'until' => strtotime($until . ' 23:59:59'),
            'limit' => 100
        ];

        // request facebook posts from meta graph api
        $response = $this->make_request('get', $page_id . '/posts', $params, $pat);
        $raw = $response['data'] ?? [];

        // handle pagination to get all posts
        while (!empty($response['paging']['next'])) {
            $next_url = $response['paging']['next'];
            $response = $this->CI->request->get($next_url);

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
                    if (stripos($p['message'] ?? '', $kw) !== false)
                        return true;
                }
                return false;
            });
            $raw = array_values($raw);
        }

        // map to ad_contents database schema
        return array_map(function ($p) {
            return [
                'title' => mb_substr($p['message'] ?? 'no caption', 0, 200),
                'content_identifier' => $p['id'],
                'published_at' => isset($p['created_time']) ? date('Y-m-d H:i:s', strtotime($p['created_time'])) : null,
                'source' => $p['permalink_url'] ?? null,
                'platform' => 'facebook',
            ];
        }, $raw);
    }

    public function fetch_insights($identifiers)
    {
        $insights = [];
        $errors = [];
        $pat = $this->get_page_access_token();

        // prepare batch for insights endpoint
        $insight_metrics_str = implode(',', $this->metrics ?? []);

        $batch_insights = [];
        foreach ($identifiers as $id) {
            $batch_insights[] = [
                'method' => 'GET',
                'relative_url' => "{$id}/insights?metric={$insight_metrics_str}&period=lifetime",
            ];
        }

        // request facebook posts insights
        $response_insights = $this->make_request('post', '', ['batch' => json_encode($batch_insights)], $pat);

        foreach ($response_insights as $index => $res) {
            $id = $identifiers[$index];
            if ($res['code'] === 200) {
                $body = json_decode($res['body'], TRUE);
                $insights[$id] = $this->normalize_insights($body['data'] ?? []);
            } else {
                $body = json_decode($res['body'], true);
                $msg = $body['error']['message'] ?? $res['body'];
                $errors[] = "[post {$id}] {$msg}";
                $time = date('Y-m-d H:i:s');
                log_message('error', "[{$time}] facebook api insight error [{$id}]: {$msg}");
                $insights[$id] = [];
            }
        }

        // prepare batch for node fields
        $fields = $this->insight_fields;
        $batch_fields = [];
        foreach ($identifiers as $id) {
            $batch_fields[] = [
                'method' => 'GET',
                'relative_url' => "{$id}?fields={$fields}",
            ];
        }

        $response_fields = $this->make_request('post', '', ['batch' => json_encode($batch_fields)], $pat);

        foreach ($response_fields as $index => $res) {
            $id = $identifiers[$index];
            if ($res['code'] !== 200) {
                continue;
            }
            $body = json_decode($res['body'], true);

            $comments = $body['comments']['summary']['total_count'] ?? 0;
            $likes = $body['likes']['summary']['total_count'] ?? 0;
            $shares = $body['shares']['count'] ?? 0;
            $reactions = $body['reactions']['summary']['total_count'] ?? 0;

            if (!isset($insights[$id])) {
                $insights[$id] = [];
            }
            $insights[$id]['total_comments'] = $comments;
            $insights[$id]['total_likes'] = $likes;
            $insights[$id]['total_shares'] = $shares;
            $insights[$id]['total_reactions'] = $reactions;

            // calculate engagement rate
            // using post_engagements OR total direct interactions
            $engaged = $insights[$id]['post_engagements'] ?? ($comments + $likes + $shares + $reactions);
            $reach = $insights[$id]['post_total_media_view_unique'] ?? 0;
            $insights[$id]['engagement_rate'] = ($reach > 0) ? round(($engaged / $reach) * 100, 2) : 0;
        }

        // throw exception if partial errors occurred
        if (!empty($errors)) {
            throw new PartialSuccessException($errors, $insights);
        }

        return $insights;
    }
}
