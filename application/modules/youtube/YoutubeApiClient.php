<?php

class YoutubeApiClient
{
    protected $base_url = 'https://www.googleapis.com/youtube/v3/';
    protected $credentials;
    protected $request;
    protected $metrics = [];

    public function __construct($credentials, $request, $metrics = [])
    {
        // initialize youtube client for youtube data api
        $this->credentials = $credentials;
        $this->request = $request;
        $this->metrics = $metrics;
    }

    protected function refresh_access_token()
    {
        $body = [
            'client_id' => $this->credentials['client_id'],
            'client_secret' => $this->credentials['client_secret'],
            'refresh_token' => $this->credentials['refresh_token'],
            'grant_type' => 'refresh_token'
        ];

        $response = $this->request->post_form('https://oauth2.googleapis.com/token', [], $body);

        if (isset($response['access_token'])) {
            $this->credentials['access_token'] = $response['access_token'];

            // update in DB using ci instance
            $CI =& get_instance();
            $CI->load->model('Platform_credential_model');

            // fetch current creds to merge properly
            $db_cred = $CI->Platform_credential_model->get_by_platform('youtube');
            $db_cred['access_token'] = $response['access_token'];
            if (isset($response['expires_in'])) {
                $db_cred['expires_at'] = time() + $response['expires_in'];
            }
            $CI->Platform_credential_model->update('youtube', $db_cred);

            return true;
        }

        throw new \RuntimeException("Failed to refresh YouTube access token.");
    }

    protected function make_api_request($url, $params)
    {
        $headers = [
            'Authorization: Bearer ' . ($this->credentials['access_token'] ?? '')
        ];

        try {
            return $this->request->get($url, $params, $headers);
        } catch (\RuntimeException $e) {
            // Check if error is 401 Unauthorized (token expired)
            if (strpos($e->getMessage(), 'HTTP 401') !== false || strpos($e->getMessage(), 'Unauthorized') !== false || strpos($e->getMessage(), 'Unauthenticated') !== false || strpos($e->getMessage(), 'invalid_grant') !== false) {
                // Refresh token and retry
                if ($this->refresh_access_token()) {
                    $headers = [
                        'Authorization: Bearer ' . $this->credentials['access_token']
                    ];
                    return $this->request->get($url, $params, $headers);
                }
            }
            throw $e;
        }
    }

    public function get_videos($since, $until, $keywords = [])
    {
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

        // check if videos is empty
        if (empty($res_videos['items']))
            return [];

        // extract video ids
        $video_ids = [];
        foreach ($res_videos['items'] as $video) {
            $video_ids[] = $video['id']['videoId'];
        }

        // set params
        $params = [
            'part' => 'snippet',
            'id' => implode(',', $video_ids),
        ];

        // request video details from youtube api
        $url = $this->base_url . 'videos';
        $res_details = $this->make_api_request($url, $params);
        $details = $res_details['items'] ?? [];

        // filtering videos based on keyword
        if (!empty($keywords)) {
            $details = array_filter($details, function ($video) use ($keywords) {
                $description = $video['snippet']['description'] ?? '';
                foreach ($keywords as $kw) {
                    if (stripos($description, $kw) !== FALSE)
                        return true;
                }
                return false;
            });
        }

        return $details;
    }

    public function get_video_stats($ids)
    {
        $params = [
            'part' => 'statistics',
            'id' => implode(',', $ids),
        ];

        // request video details from youtube api
        $url = $this->base_url . 'videos';
        $response = $this->make_api_request($url, $params);
        $insights = $response['items'] ?? [];

        // extract video stats
        $result = [];
        foreach ($insights as $index => $value) {
            $stats = $value['statistics'] ?? [];
            if (!empty($this->metrics)) {
                $filtered = [];
                foreach ($this->metrics as $m) {
                    if (isset($stats[$m])) {
                        $filtered[$m] = $stats[$m];
                    }
                }
                $result[$ids[$index]] = $filtered;
            } else {
                unset($stats['favoriteCount']);
                $result[$ids[$index]] = $stats;
            }
        }

        return $result;
    }
}
