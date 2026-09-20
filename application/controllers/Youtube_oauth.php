<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Youtube_oauth extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->require_superadmin();
        $this->load->model('Platform_credential_model');
        $this->load->library('request');
    }

    public function login()
    {
        $cred = $this->Platform_credential_model->get_by_platform('youtube');

        if (empty($cred['client_id']) || empty($cred['client_secret'])) {
            $this->session->set_flashdata('errors', "YouTube client_id and client_secret are not set in the database.");
            redirect('config/platforms/youtube');
            return;
        }

        $redirect_uri = site_url('youtube_oauth/callback');

        $params = [
            'client_id' => $cred['client_id'],
            'redirect_uri' => $redirect_uri,
            'response_type' => 'code',
            'scope' => 'https://www.googleapis.com/auth/youtube.readonly',
            'access_type' => 'offline',
            'prompt' => 'consent'
        ];

        $url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
        redirect($url);
    }

    public function callback()
    {
        $code = $this->input->get('code');
        $error = $this->input->get('error');

        if ($error) {
            $this->session->set_flashdata('errors', "OAuth Error: " . htmlspecialchars($error));
            redirect('config/platforms/youtube');
            return;
        }

        if (!$code) {
            $this->session->set_flashdata('errors', "No authorization code provided.");
            redirect('config/platforms/youtube');
            return;
        }

        $cred = $this->Platform_credential_model->get_by_platform('youtube');

        if (empty($cred['client_id']) || empty($cred['client_secret'])) {
            $this->session->set_flashdata('errors', "YouTube client_id and client_secret are missing in the database.");
            redirect('config/platforms/youtube');
            return;
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

                $this->session->set_flashdata('success', "Authentication successful! Access token and refresh token have been retrieved.");
            } else {
                $this->session->set_flashdata('errors', "Failed to exchange access token with Google.");
            }
        } catch (\Exception $e) {
            $this->session->set_flashdata('errors', "Token exchange failed: " . htmlspecialchars($e->getMessage()));
        }

        redirect('config/platforms/youtube');
    }
}
