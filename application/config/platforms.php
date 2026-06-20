<?php
defined('BASEPATH') or exit('No direct script access allowed');

$config['platforms'] = [
    'facebook' => [
        'enabled' => true,
        'label' => 'Facebook',
        'driver_class' => 'FacebookDriver',
        'driver_path' => APPPATH . 'modules/facebook/FacebookDriver.php',
        'metrics' => [
            'post_media_view',
            'post_clicks',
            'post_reactions_by_type_total',
            'post_total_media_view_unique',
            'post_engaged_users',
            'post_impressions_unique',
        ],
        'filters' => [
            'keyword' => true,
            'hostname' => false,
            'html' => false,
        ],
    ],
    'instagram' => [
        'enabled' => true,
        'label' => 'Instagram',
        'driver_class' => 'InstagramDriver',
        'driver_path' => APPPATH . 'modules/instagram/InstagramDriver.php',
        'metrics' => [
            'reach',
            'saved',
            'shares',
            'likes',
            'comments',
            'total_interactions',
            'profile_activity',
        ],
        'reels_metrics' => [
            'ig_reels_avg_watch_time',
            'ig_reels_video_view_total_time',
            'reels_skip_rate',
        ],
        'filters' => [
            'keyword' => true,
            'hostname' => false,
            'html' => false,
        ],
    ],
    'youtube' => [
        'enabled' => true,
        'label' => 'YouTube',
        'driver_class' => 'YoutubeDriver',
        'driver_path' => APPPATH . 'modules/youtube/YoutubeDriver.php',
        'metrics' => [
            'viewCount',
            'likeCount',
            'commentCount',
        ],
        'filters' => [
            'keyword' => true,
            'hostname' => false,
            'html' => false,
        ],
    ],
    'ga4' => [
        'enabled' => true,
        'label' => 'Google Analytics 4',
        'driver_class' => 'Ga4Driver',
        'driver_path' => APPPATH . 'modules/ga4/Ga4Driver.php',
        'filters' => [
            'keyword' => false,
            'hostname' => true,
            'html' => true,
        ],
        'metrics' => [
            'screenPageViews',
            'activeUsers',
            'averageSessionDuration',
            'engagementRate',
            'sessions',
            'engagedSessions',
            'newUsers',
        ],
    ],
];