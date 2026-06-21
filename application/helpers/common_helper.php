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

if (!function_exists('send_email')) {
    /**
     * @param string|array $to Recipient email address(es)
     * @param string $subject Email subject
     * @param string $message Email HTML message
     * @return bool
     */
    function send_email($to, $subject, $message)
    {
        $CI =& get_instance();
        $CI->load->library('email');

        $from_email = $_ENV['SMTP_USER'] ?? 'no-reply@example.com';
        $from_name = $_ENV['SMTP_FROM_NAME'] ?? 'Ads Aggregator';

        $CI->email->clear(TRUE);
        $CI->email->from($from_email, $from_name);
        $CI->email->to($to);
        $CI->email->subject($subject);
        $CI->email->message($message);

        if ($CI->email->send()) {
            return true;
        }

        log_message('error', 'Email Send Error: ' . $CI->email->print_debugger(['headers']));
        return false;
    }
}
