<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Youtube_oauth extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Platform_credential_model');
        $this->load->library('request');
    }

    public function login()
    {
        $cred = $this->Platform_credential_model->get_by_platform('youtube');

        if (empty($cred['client_id']) || empty($cred['client_secret'])) {
            show_error("YouTube client_id and client_secret are not set in the database.", 500);
        }

        $redirect_uri = site_url('youtube_oauth/callback');

        $params = [
            'client_id' => $cred['client_id'],
            'redirect_uri' => $redirect_uri,
            'response_type' => 'code',
            'scope' => 'https://www.googleapis.com/auth/youtube.readonly',
            'access_type' => 'offline',
            'prompt' => 'consent' // Forces Google to always return a refresh token
        ];

        $url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
        redirect($url);
    }

    private function _render_view($status, $message)
    {
        $data['title'] = 'YouTube OAuth';
        $data['status'] = $status;
        $data['message'] = $message;
        $data['current_account'] = $this->current_account;

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('platforms/youtube_oauth_result', $data);
        $this->load->view('templates/footer');
    }

    public function callback()
    {
        $code = $this->input->get('code');
        $error = $this->input->get('error');

        if ($error) {
            return $this->_render_view('error', "OAuth Error: " . htmlspecialchars($error));
        }

        if (!$code) {
            return $this->_render_view('error', "No authorization code provided.");
        }

        $cred = $this->Platform_credential_model->get_by_platform('youtube');

        if (empty($cred['client_id']) || empty($cred['client_secret'])) {
            return $this->_render_view('error', "YouTube client_id and client_secret are missing in the database.");
        }

        $redirect_uri = site_url('youtube_oauth/callback');

        $body = [
            'code' => $code,
            'client_id' => $cred['client_id'],
            'client_secret' => $cred['client_secret'],
            'redirect_uri' => $redirect_uri,
            'grant_type' => 'authorization_code'
        ];

        try {
            // Exchange code for tokens
            $response = $this->request->post_form('https://oauth2.googleapis.com/token', [], $body);

            if (isset($response['access_token'])) {
                $cred['access_token'] = $response['access_token'];

                if (isset($response['refresh_token'])) {
                    $cred['refresh_token'] = $response['refresh_token'];
                }

                if (isset($response['expires_in'])) {
                    $cred['expires_at'] = time() + $response['expires_in'];
                }

                $this->Platform_credential_model->update('youtube', $cred);

                return $this->_render_view('success', "Otentikasi berhasil! Token akses dan refresh token telah didapatkan.");
            } else {
                return $this->_render_view('error', "Gagal menukar token akses dengan Google.");
            }
        } catch (\Exception $e) {
            return $this->_render_view('error', "Token exchange failed: " . htmlspecialchars($e->getMessage()));
        }
    }
}
