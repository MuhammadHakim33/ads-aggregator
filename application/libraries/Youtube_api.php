<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Youtube_api
{
    const BASE_URL = 'https://www.googleapis.com/youtube/v3/';

    private $api_key;
    private $channel_id;
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->library('request');

        $this->api_key = $_ENV['YOUTUBE_API_KEY'];
        $this->channel_id = $_ENV['YOUTUBE_CHANNEL_ID'];
    }

    public function get_videos($published_after, $published_before, $keyword_filters = [])
    {
        // set url
        $url = self::BASE_URL . 'search';

        // format dates to RFC 3339 (required by YouTube API)
        $published_after = gmdate('Y-m-d\TH:i:s\Z', strtotime($published_after));
        $published_before = gmdate('Y-m-d\TH:i:s\Z', strtotime($published_before));

        // set params
        $params = [
            'part'           => 'id',
            'channelId'      => $this->channel_id,
            'type'           => 'video',
            'publishedAfter' => $published_after,
            'publishedBefore'=> $published_before,
            'maxResults'     => 50,
            'key'            => $this->api_key,
        ];

        // request video id from youtube api
        $videos = $this->CI->request->get($url, $params);

        // check if videos is empty
        if (empty($videos['items'])) return [];
        
        // extract video ids
        $video_ids = [];
        foreach ($videos['items'] as $video) {
            $video_ids[] = $video['id']['videoId'];
        }

        // set url
        $url = self::BASE_URL . 'videos';

        // set params
        $params = [
            'part' => 'snippet',
            'id'   => implode(',', $video_ids),
            'key'  => $this->api_key,
        ];

        // request video details from youtube api
        $video_details = $this->CI->request->get($url, $params);

        // if empty keyword filters, return all videos directly
        if (empty($keyword_filters)) {
            $result = [];
            foreach ($video_details['items'] as $video) {
                $result[] = [   
                    'video_id' => $video['id'],
                    'title'=> $video['snippet']['title'],
                    'description' => $video['snippet']['description'],
                    'published_at' => $video['snippet']['publishedAt'],
                ];
            }
            return $result;
        }

        // extract and filter video details
        $result = [];
        foreach ($video_details['items'] as $video) {
            $description = $video['snippet']['description'] ?? '';
            foreach ($keyword_filters as $kw) {
                // if video description contains keyword, add to result
                if (stripos($description, $kw) !== FALSE) {
                    $result[] = [   
                        'video_id' => $video['id'],
                        'title'=> $video['snippet']['title'],
                        'description' => $video['snippet']['description'],
                        'published_at' => $video['snippet']['publishedAt'],
                    ];
                    break;
                }
            }
        }


        return $result;
    }

    public function get_video_insights($video_ids)
    {
        // set url
        $url = self::BASE_URL . 'videos';

        // set params
        $params = [
            'part' => 'statistics',
            'id'   => implode(',', $video_ids),
            'key'  => $this->api_key,
        ];

        // request video details from youtube api
        $video_details = $this->CI->request->get($url, $params);

        // check if video details is empty
        if (empty($video_details['items'])) return [];

        // extract video insights to array
        $video_insights = [];
        foreach ($video_details['items'] as $index => $video) {
            $id = $video_ids[$index];
            $video_insights[$id] = $video['statistics'];
        }

        return $video_insights;
    }
}