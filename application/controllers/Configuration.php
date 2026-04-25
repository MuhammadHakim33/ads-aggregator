<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Configuration extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Filter_keyword_model');
    }

    public function filter_keywords($platform = null)
    {
        $keywords = $this->Filter_keyword_model->get_all($platform);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'data' => $keywords
            ]));
    }

    public function create_filter_keyword()
    {
        if ($this->input->method() !== 'post') {
            return $this->output
                ->set_status_header(405)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Method Not Allowed. Please use POST.']));
        }

        $this->form_validation->set_rules([
            [
                'field' => 'platform',
                'label' => 'Platform',
                'rules' => 'required|in_list[meta,gam,ga4,yt]'
            ],
            [
                'field' => 'keyword',
                'label' => 'Keyword',
                'rules' => 'required|trim|min_length[2]|max_length[255]'
            ]
        ]);

        if ($this->form_validation->run() === FALSE) {
            return $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => validation_errors()]));
        }

        $insert_id = $this->Filter_keyword_model->insert([
            'platform'     => $this->input->post('platform'),
            'keyword'    => $this->input->post('keyword'),
            'is_active' => TRUE
        ]);

        if ($insert_id) {
            $this->output
                ->set_status_header(201)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'success',
                    'message' => 'Filter keyword created successfully',
                    'data' => ['id' => $insert_id]
                ]));
        } else {
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Failed to create filter keyword']));
        }
    }

    public function update_filter_keyword($id)
    {
        if (!$id) {
            return $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Filter ID is required']));
        }

        if ($this->Filter_keyword_model->is_exist_by_id($id) == 0) {
            return $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Filter not found']));
        }

        $data = [];

        $platform = $this->input->input_stream('platform', TRUE);
        $keyword = $this->input->input_stream('keyword', TRUE);
        $is_active = $this->input->input_stream('is_active', TRUE);

        if ($platform !== null) $data['platform'] = $platform;
        if ($keyword !== null) $data['keyword'] = $keyword;
        if ($is_active !== null) $data['is_active'] = $is_active;

        $this->form_validation->set_data($data);

        $this->form_validation->set_rules([
            [
                'field' => 'platform',
                'label' => 'Platform',
                'rules' => 'in_list[meta,gam,ga4,yt]'
            ],
            [
                'field' => 'keyword',
                'label' => 'Keyword',
                'rules' => 'min_length[2]|max_length[255]'
            ],
            [
                'field' => 'is_active',
                'label' => 'Active Status',
                'rules' => 'in_list[0,1]'
            ]
        ]);

        if ($this->form_validation->run() === FALSE) {
            return $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => validation_errors()]));
        }

        if (empty($data)) {
            return $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'No data to update']));
        }

        $updated = $this->Filter_keyword_model->update($id, $data);

        if ($updated) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'success', 'message' => 'Filter keyword updated successfully']));
        } else {
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Failed to update filter keyword']));
        }
    }

    public function delete_filter_keyword($id)
    {
        if (!$id) {
            return $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Filter ID is required']));
        }

        if ($this->Filter_keyword_model->is_exist_by_id($id) == 0) {
            return $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Filter keyword not found']));
        }

        $deleted = $this->Filter_keyword_model->delete($id);

        if ($deleted) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'success', 'message' => 'Filter keyword deleted successfully']));
        } else {
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Failed to delete filter keyword']));
        }
    }
}
