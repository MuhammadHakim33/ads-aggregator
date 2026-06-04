<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Credentials extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Platform_credential_model');

        $this->require_superadmin();
    }

    public function index()
    {
        $credential = $this->Platform_credential_model->get_all();

        // index by platform_code for easy access in view
        $credentials = [];
        foreach ($credential as $row) {
            $credentials[$row->platform] = $row;
        }

        $data = [
            'title' => 'API Credentials',
            'active_menu' => 'api',
            'credentials' => $credentials,
        ];

        $this->render('configuration/credentials/index', $data);
    }

    public function save($platform)
    {
        if ($this->input->method() !== 'post') {
            redirect('config/credentials');
        }

        // check url path contain platform
        $allowed = ['meta', 'ga4', 'youtube'];
        if (!in_array($platform, $allowed)) {
            $this->session->set_flashdata('errors', '<p>Platform not valid. Please check URL</p>');
            redirect('config/credentials');
            return;
        }

        $raw = $this->input->post('credential_json');

        // validate JSON
        $decoded = json_decode($raw, TRUE);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->session->set_flashdata('errors', '<p>Wrong credential format. Please check again</p>');
            redirect('config/credentials');
            return;
        }

        // check if credential already exists
        $is_exists = $this->Platform_credential_model->is_exists($platform);
        if ($is_exists) {
            // if exists, update it
            $updated = $this->Platform_credential_model->update($platform, $decoded);
            if ($updated) {
                $this->session->set_flashdata('success', 'Credential ' . strtoupper($platform) . ' has been updated.');
            } else {
                $this->session->set_flashdata('errors', '<p>Failed to update credential. Please try again.</p>');
            }

            redirect('config/credentials');
            return;
        }

        // if not exists, insert it
        $saved = $this->Platform_credential_model->insert($platform, $decoded);
        if ($saved) {
            $this->session->set_flashdata('success', 'Credential ' . strtoupper($platform) . ' has been saved.');
        } else {
            $this->session->set_flashdata('errors', '<p>Failed to save credential. Please try again.</p>');
        }

        redirect('config/credentials');
    }
}
