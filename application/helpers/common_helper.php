<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('generate_ad_post_url')) {
    function generate_ad_post_url($platform, $content_identifier)
    {
        if (empty($content_identifier)) {
            return '#';
        }

        $platform = strtolower(trim($platform));
        $cid = trim($content_identifier);

        switch ($platform) {
            case 'facebook':
                return 'https://www.facebook.com/' . $cid;
            case 'instagram':
                return 'https://www.instagram.com/p/' . $cid . '/';
            case 'youtube':
                return 'https://www.youtube.com/watch?v=' . $cid;
            case 'ga4':
                return 'https://' . $cid;
            default:
                return '#';
        }
    }
}
