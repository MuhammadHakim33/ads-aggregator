<?php
require_once APPPATH . 'core/Platform_driver.php';

class YoutubeDriver extends Platform_driver
{
    protected $base_url = 'https://www.googleapis.com/youtube/v3/';
    protected $credentials;
    protected $metrics = [];

    public function __construct()
    {
        parent::__construct();
        $this->CI->config->load('platforms');

        $cred = $this->CI->Platform_credential_model->get_by_platform('youtube');
        if (empty($cred) || empty($cred['client_id']) || empty($cred['client_secret']) || empty($cred['channel_id'])) {
            throw new \Exception("credentials for youtube are empty or incomplete. please ensure client_id, client_secret, and channel_id are set in the database.");
        }

        if (empty($cred['access_token']) || empty($cred['refresh_token'])) {
            throw new \Exception("youtube oauth tokens are missing. please authorize the application by navigating to /youtube_oauth/login.");
        }

        $this->credentials = $cred;
        $platform_config = $this->CI->config->item('platforms')['youtube'] ?? [];
        $this->metrics = $platform_config['metrics'] ?? [];
    }

    public function name()
    {
        return 'youtube';
    }

    protected function refresh_access_token()
    {
        $body = [
            'client_id' => $this->credentials['client_id'],
            'client_secret' => $this->credentials['client_secret'],
            'refresh_token' => $this->credentials['refresh_token'],
            'grant_type' => 'refresh_token'
        ];

        // request new token
        $response = $this->CI->request->post_form('https://oauth2.googleapis.com/token', [], $body);

        if (isset($response['access_token'])) {
            $this->credentials['access_token'] = $response['access_token'];

            // fetch current creds to merge properly
            $db_cred = $this->CI->Platform_credential_model->get_by_platform('youtube');
            $db_cred['access_token'] = $response['access_token'];
            if (isset($response['expires_in'])) {
                $db_cred['expires_at'] = time() + $response['expires_in'];
            }
            $this->CI->Platform_credential_model->update('youtube', $db_cred);

            return true;
        }

        throw new \RuntimeException("failed to refresh youtube access token.");
    }

    protected function make_api_request($url, $params)
    {
        $headers = [
            'Authorization: Bearer ' . ($this->credentials['access_token'] ?? '')
        ];

        try {
            return $this->CI->request->get($url, $params, $headers);
        } catch (\RuntimeException $e) {
            // check if error is 401 unauthorized (token expired)
            $msg = strtolower($e->getMessage());
            if (strpos($msg, 'http 401') !== false || strpos($msg, 'unauthorized') !== false || strpos($msg, 'unauthenticated') !== false || strpos($msg, 'invalid_grant') !== false) {
                // refresh token and retry
                if ($this->refresh_access_token()) {
                    $headers = [
                        'Authorization: Bearer ' . $this->credentials['access_token']
                    ];
                    return $this->CI->request->get($url, $params, $headers);
                }
            }
            throw $e;
        }
    }

    public function fetch_contents($since, $until, $filters = [])
    {
        $keywords = $filters['keywords'] ?? [];
        $params = [
            'part' => 'id',
            'channelId' => $this->credentials['channel_id'],
            'type' => 'video',
            'publishedAfter' => gmdate('Y-m-d\T00:00:00\Z', strtotime($since)),
            'publishedBefore' => gmdate('Y-m-d\T23:59:59\Z', strtotime($until)),
            'maxResults' => 50,
        ];

        // request video id from youtube api
        $url = $this->base_url . 'search';
        $res_videos = $this->make_api_request($url, $params);

        log_message('info', "[YoutubeDriver::fetch_contents::res_video] " . json_encode($res_videos));

        // check if videos is empty
        if (empty($res_videos['items'])) {
            return [];
        }

        // extract video ids
        $video_ids = [];
        foreach ($res_videos['items'] as $video) {
            $video_ids[] = $video['id']['videoId'];
        }

        // set params for video details
        $params = [
            'part' => 'snippet',
            'id' => implode(',', $video_ids),
        ];

        // request video details from youtube api
        $url = $this->base_url . 'videos';
        $res_details = $this->make_api_request($url, $params);
        $details = $res_details['items'] ?? [];

        log_message('info', "[YoutubeDriver::fetch_contents::res_details] " . json_encode($details));

        // filter videos based on keyword
        if (!empty($keywords)) {
            $details = array_filter($details, function ($video) use ($keywords) {
                $description = $video['snippet']['description'] ?? '';
                foreach ($keywords as $kw) {
                    if (stripos($description, $kw) !== false)
                        return true;
                }
                return false;
            });
        }

        // map to ad_contents database schema
        return array_map(function ($p) {
            return [
                'title' => mb_substr($p['snippet']['title'] ?? '', 0, 200),
                'content_identifier' => $p['id'] ?? '',
                'published_at' => isset($p['snippet']['publishedAt']) ? date('Y-m-d H:i:s', strtotime($p['snippet']['publishedAt'])) : null,
                'source' => isset($p['id']) ? 'https://www.youtube.com/watch?v=' . $p['id'] : null,
                'platform' => 'youtube',
            ];
        }, array_values($details));
    }

    public function fetch_insights($identifiers)
    {
        $params = [
            'part' => 'statistics',
            'id' => implode(',', $identifiers),
        ];

        // request video stats from youtube api
        $url = $this->base_url . 'videos';
        $response = $this->make_api_request($url, $params);
        $items = $response['items'] ?? [];

        log_message('info', "[YoutubeDriver::fetch_insights::response] " . json_encode($response));

        // extract video stats
        $insights = [];
        foreach ($items as $index => $value) {
            $stats = $value['statistics'] ?? [];
            $filtered = [];

            foreach ($this->metrics as $m) {
                if (isset($stats[$m])) {
                    $filtered[$m] = $stats[$m];
                }
            }

            // calculate engagement rate
            $likes = $filtered['likeCount'] ?? 0;
            $comments = $filtered['commentCount'] ?? 0;
            $views = $filtered['viewCount'] ?? 0;
            $engagements = $likes + $comments;
            $filtered['engagement_rate'] = ($views > 0) ? round(($engagements / $views) * 100, 2) : 0;

            $insights[$identifiers[$index]] = $filtered;
        }

        return $insights;
    }
}
