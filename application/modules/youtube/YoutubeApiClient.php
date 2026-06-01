<?php

class YoutubeApiClient
{
    protected $base_url = 'https://www.googleapis.com/youtube/v3/';
    protected $credentials;
    protected $request;

    public function __construct($credentials, $request)
    {
        // initialize youtube client for youtube data api
        $this->credentials = $credentials;
        $this->request = $request;
    }

    public function get_videos($since, $until, $keywords = [])
    {
        $params = [
            'part' => 'id',
            'channelId' => $this->credentials['channel_id'],
            'type' => 'video',
            'publishedAfter' => gmdate('Y-m-d\T00:00:00\Z', strtotime($since)),
            'publishedBefore'=> gmdate('Y-m-d\T23:59:59\Z', strtotime($until)),
            'maxResults' => 50,
            'key' => $this->credentials['api_key'],
        ];

        // request video id from youtube api
        $url = $this->base_url . 'search';
        $res_videos = $this->request->get($url, $params);
        
        // check if videos is empty
        if (empty($res_videos['items'])) return [];

        // extract video ids
        $video_ids = [];
        foreach ($res_videos['items'] as $video) {
            $video_ids[] = $video['id']['videoId'];
        }

        // set params
        $params = [
            'part' => 'snippet',
            'id' => implode(',', $video_ids),
            'key' => $this->credentials['api_key'],
        ];
        
        // request video details from youtube api
        $url = $this->base_url . 'videos';
        $res_details = $this->request->get($url, $params);
        $details = $res_details['items'] ?? [];

        // filtering videos based on keyword
        if (!empty($keywords)) {
            $details = array_filter($details, function($video) use ($keywords) {
                $description = $video['snippet']['description'] ?? '';
                foreach ($keywords as $kw) {
                    if (stripos($description, $kw) !== FALSE) return true;
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
            'key' => $this->credentials['api_key'],
        ];

        // request video details from youtube api
        $url = $this->base_url . 'videos';
        $response = $this->request->get($url, $params);
        $insights = $response['items'] ?? [];

        // extract video stats
        $result = [];
        foreach ($insights as $index => $value) {
            $result[$ids[$index]] = $value['statistics'];
        }
        
        return $result;
    }
}
