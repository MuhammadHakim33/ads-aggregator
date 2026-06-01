<?php
defined('BASEPATH') or exit('No direct script access allowed');

$config['platforms'] = [
    'facebook' => [
        'enabled' => true,
        'label' => 'Facebook',
        'icon' => 'bi-facebook',
        'color' => 'primary',
        'api_group' => 'meta',
        'driver_class' => 'FacebookDriver',
        'driver_path' => APPPATH . 'modules/facebook/FacebookDriver.php',
        'credential_schema' => [
            'system_user_token' => 'System User Token',
            'fb_page_id' => 'Facebook Page ID',
        ],
    ],
    'instagram' => [
        'enabled' => true,
        'label' => 'Instagram',
        'icon' => 'bi-instagram',
        'color' => 'danger',
        'api_group' => 'meta',
        'driver_class' => 'InstagramDriver',
        'driver_path' => APPPATH . 'modules/instagram/InstagramDriver.php',
        'credential_schema' => [
            'ig_account_id' => 'Instagram Account ID',
        ],
    ],
    'youtube' => [
        'enabled' => true,
        'label' => 'YouTube',
        'icon' => 'bi-youtube',
        'color' => 'danger',
        'api_group' => 'youtube',
        'driver_class' => 'YoutubeDriver',
        'driver_path' => APPPATH . 'modules/youtube/YoutubeDriver.php',
        'credential_schema' => [
            'api_key' => 'API Key',
            'channel_id' => 'Channel ID',
        ],
    ],
    'ga4' => [
        'enabled' => true,
        'label' => 'Google Analytics 4',
        'icon' => 'bi-bar-chart-line',
        'color' => 'success',
        'api_group' => 'ga4',
        'driver_class' => 'Ga4Driver',
        'driver_path' => APPPATH . 'modules/ga4/Ga4Driver.php',
        'credential_schema' => [
            'property_id' => 'Property ID',
            'service_account' => 'Service Account JSON',
        ],
        'supports_hostname_filter' => true,
        'supports_html_filter' => true,
    ],
];