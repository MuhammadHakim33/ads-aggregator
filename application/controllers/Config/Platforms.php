<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Platforms extends MY_Controller
{
    private $credential_platforms = ['meta', 'ga4', 'youtube', 'gam'];

    private $platform_keyword_map = [
        'meta' => ['facebook', 'instagram'],
        'ga4' => ['ga4'],
        'youtube' => ['youtube'],
        'gam' => ['gam'],
    ];

    private $keyword_types = ['html', 'keyword', 'hostname'];

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Platform_credential_model');
        $this->load->model('Filter_keyword_model');
        $this->require_superadmin();
    }

    public function meta()
    {
        $this->_render_platform('meta');
    }

    public function ga4()
    {
        $this->_render_platform('ga4');
    }

    public function youtube()
    {
        $this->_render_platform('youtube');
    }

    public function gam()
    {
        $this->_render_platform('gam');
    }

    public function save_credential($platform)
    {
        if ($this->input->method() !== 'post') {
            redirect('config/platforms/' . $platform);
        }

        if (!in_array($platform, $this->credential_platforms)) {
            $this->session->set_flashdata('errors', '<div>Invalid platform.</div>');
            redirect('dashboard');
            return;
        }

        $raw = $this->input->post('credential_json');

        // validate JSON
        $decoded = json_decode($raw, TRUE);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->session->set_flashdata('errors', '<div>Invalid JSON format. Please check your credential and try again.</div>');
            redirect('config/platforms/' . $platform);
            return;
        }

        try {
            $is_exists = $this->Platform_credential_model->is_exists($platform);
            if ($is_exists) {
                $ok = $this->Platform_credential_model->update($platform, $decoded);
                $msg = strtoupper($platform) . ' credential updated successfully.';
            } else {
                $ok = $this->Platform_credential_model->insert($platform, $decoded);
                $msg = strtoupper($platform) . ' credential saved successfully.';
            }

            if ($ok) {
                $this->session->set_flashdata('success', $msg);
            } else {
                $this->session->set_flashdata('errors', '<div>Failed to save credential. Please try again.</div>');
            }
        } catch (\RuntimeException $e) {
            $this->session->set_flashdata('errors', '<div><strong>Encryption failed:</strong> ' . htmlspecialchars($e->getMessage()) . '</div>');
        }

        redirect('config/platforms/' . $platform);
    }

    public function create_keyword($platform)
    {
        if (!array_key_exists($platform, $this->platform_keyword_map)) {
            show_404();
            return;
        }

        if ($this->input->method() !== 'post') {
            redirect('config/platforms/' . $platform);
            return;
        }

        // Build platform-specific payload and validation rules
        $payload = $this->_build_keyword_payload($platform);
        $errors = $this->_validate_keyword_payload($platform, $payload);

        if ($errors) {
            $this->session->set_flashdata('errors', '<div>' . implode('</div><div>', $errors) . '</div>');
            redirect('config/platforms/' . $platform);
            return;
        }

        $payload['is_active'] = TRUE;
        $insert_id = $this->Filter_keyword_model->insert($payload);

        if ($insert_id) {
            $this->session->set_flashdata('success', 'Filter keyword created successfully.');
        } else {
            $this->session->set_flashdata('errors', '<div>Failed to create filter keyword. Please try again.</div>');
        }

        redirect('config/platforms/' . $platform);
    }

    public function edit_keyword($platform, $id)
    {
        if (!array_key_exists($platform, $this->platform_keyword_map)) {
            show_404();
            return;
        }

        $keyword = $this->Filter_keyword_model->get_by_id($id);
        if (!$keyword) {
            $this->session->set_flashdata('errors', '<div>Filter keyword not found.</div>');
            redirect('config/platforms/' . $platform);
            return;
        }

        if ($this->input->method() !== 'post') {
            redirect('config/platforms/' . $platform);
            return;
        }

        $payload = $this->_build_keyword_payload($platform, $keyword);
        $errors = $this->_validate_keyword_payload($platform, $payload);

        if ($errors) {
            $this->session->set_flashdata('errors', '<div>' . implode('</div><div>', $errors) . '</div>');
            redirect('config/platforms/' . $platform);
            return;
        }

        $is_active = $this->input->post('is_active');
        $payload['is_active'] = in_array($is_active, ['0', '1']) ? (int) $is_active : 1;

        $updated = $this->Filter_keyword_model->update($id, $payload);

        if ($updated) {
            $this->session->set_flashdata('success', 'Filter keyword updated successfully.');
        } else {
            $this->session->set_flashdata('errors', '<div>Failed to update filter keyword. Please try again.</div>');
        }

        redirect('config/platforms/' . $platform);
    }

    public function delete_keyword($platform, $id)
    {
        if ($this->input->method() !== 'post') {
            redirect('config/platforms/' . $platform);
            return;
        }

        if (!array_key_exists($platform, $this->platform_keyword_map)) {
            show_404();
            return;
        }

        if ($this->Filter_keyword_model->is_exist_by_id($id) == 0) {
            $this->session->set_flashdata('errors', '<div>Filter keyword not found.</div>');
            redirect('config/platforms/' . $platform);
            return;
        }

        $deleted = $this->Filter_keyword_model->delete($id);

        if ($deleted) {
            $this->session->set_flashdata('success', 'Filter keyword deleted successfully.');
        } else {
            $this->session->set_flashdata('errors', '<div>Failed to delete filter keyword. Please try again.</div>');
        }

        redirect('config/platforms/' . $platform);
    }

    private function _build_keyword_payload($platform, $existing = NULL)
    {
        $keyword = trim($this->input->post('keyword'));

        switch ($platform) {
            case 'youtube':
                return [
                    'platform' => 'youtube',
                    'type' => 'keyword',
                    'keyword' => $keyword,
                ];

            case 'gam':
                return [
                    'platform' => 'gam',
                    'type' => 'keyword',
                    'keyword' => $keyword,
                ];

            case 'meta':
                return [
                    'platform' => 'facebook',
                    'type' => 'keyword',
                    'keyword' => $keyword,
                ];

            case 'ga4':
                return [
                    'platform' => 'ga4',
                    'type' => $this->input->post('type'),
                    'keyword' => $keyword,
                ];

            default:
                return [];
        }
    }

    private function _validate_keyword_payload($platform, array $payload)
    {
        $errors = [];

        $keyword = $payload['keyword'] ?? '';
        if (strlen($keyword) < 2 || strlen($keyword) > 255) {
            $errors[] = 'Keyword must be between 2 and 255 characters.';
        }

        if ($platform === 'ga4') {
            $allowed_types = ['hostname', 'html'];
            if (!in_array($payload['type'] ?? '', $allowed_types)) {
                $errors[] = 'Type must be hostname or html.';
            }
        }

        return $errors;
    }

    private function _render_platform($platform)
    {
        if (!array_key_exists($platform, $this->platform_keyword_map)) {
            show_404();
            return;
        }

        $credential_json = NULL;
        if (in_array($platform, $this->credential_platforms)) {
            if ($this->Platform_credential_model->is_exists($platform)) {
                $credential_json = $this->Platform_credential_model->get_raw_decrypted($platform);
            }
        }

        $kw_platforms = $this->platform_keyword_map[$platform];
        $keywords = $this->Filter_keyword_model->get_by_platforms_admin($kw_platforms);

        $labels = [
            'meta' => 'Meta (Facebook + Instagram)',
            'ga4' => 'Google Analytics 4',
            'youtube' => 'YouTube',
            'gam' => 'Google Ad Manager',
        ];

        $data = [
            'title' => $labels[$platform] ?? strtoupper($platform),
            'active_menu' => 'platform_' . $platform,
            'platform' => $platform,
            'platform_label' => $labels[$platform] ?? strtoupper($platform),
            'credential' => $credential_json,
            'keywords' => $keywords,
            'kw_platforms' => $kw_platforms,
            'keyword_types' => $this->keyword_types,
        ];

        $this->render('platforms/' . $platform, $data);
    }
}
