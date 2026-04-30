<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Youtube
{
    const BASE_URL = 'https://www.googleapis.com/youtube/v3/';

    private $api_key;
    private $channel_id;

    public function __construct()
    {
        $this->api_key    = $_ENV['YOUTUBE_API_KEY'];
        $this->channel_id = $_ENV['YOUTUBE_CHANNEL_ID'];
    }

    private function request($endpoint, $params = [])
    {
        $url = self::BASE_URL . $endpoint;
        $url .= '?' . http_build_query($params);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        ]);

        $response   = curl_exec($ch);
        $http_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($curl_error) throw new \RuntimeException("cURL error: $curl_error");

        $data = json_decode($response, TRUE);

        if ($http_code !== 200 || isset($data['error'])) {
            $message = $data['error']['message'] ?? "HTTP $http_code";
            throw new \RuntimeException("YouTube API error: $message");
        }

        return $data;
    }

    private function search_video_ids($published_after, $published_before)
    {
        $response = $this->request('search', [
            'part'           => 'id',
            'channelId'      => $this->channel_id,
            'type'           => 'video',
            'publishedAfter' => $published_after,   // format: RFC 3339, e.g. 2025-01-01T00:00:00Z
            'publishedBefore'=> $published_before,
            'maxResults'     => 50,
            'key'            => $this->api_key,
        ]);

        if (empty($response['items'])) return [];

        return array_column(array_column($response['items'], 'id'), 'videoId');
    }

    private function get_video_details($video_ids)
    {
        $response = $this->request('videos', [
            'part' => 'snippet,statistics',
            'id'   => implode(',', $video_ids),
            'key'  => $this->api_key,
        ]);

        return $response['items'] ?? [];
    }

    public function get_videos($published_after, $published_before)
    {
        $video_ids = $this->search_video_ids($published_after, $published_before);

        if (empty($video_ids)) return [];

        return $this->get_video_details($video_ids);
    }
}