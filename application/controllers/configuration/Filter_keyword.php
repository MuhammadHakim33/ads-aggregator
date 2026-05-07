<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Filter_keyword extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Filter_keyword_model');

        // require login
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }

        // redirect if role is not superadmin
        if ($this->session->userdata('role') !== 'superadmin') {
            redirect('welcome');
        }
    }

    public function index()
    {
        $data = [
            'title' => 'Filter Keywords',
            'active_menu' => 'configuration',
            'current_account' => [
                'name' => $this->session->userdata('name'),
                'role' => $this->session->userdata('role')
            ],
            'keywords' => $this->Filter_keyword_model->get_all_admin()
        ];

        $this->load->view('configuration/filter_keyword/index', $data);
    }

    public function create()
    {
        if ($this->input->method() === 'post') {
            // set validation rules
            $this->load->library('form_validation');
            $this->form_validation->set_rules([
                [
                    'field' => 'platform',
                    'label' => 'Platform',
                    'rules' => 'required|in_list[facebook,instagram,gam,ga4,youtube]'
                ],
                [
                    'field' => 'type',
                    'label' => 'Type',
                    'rules' => 'required|in_list[html,keyword,hostname]'
                ],
                [
                    'field' => 'keyword',
                    'label' => 'Keyword',
                    'rules' => 'required|trim|min_length[2]|max_length[255]'
                ]
            ]);

            if ($this->form_validation->run() === TRUE) {
                $insert_id = $this->Filter_keyword_model->insert([
                    'platform'  => $this->input->post('platform'),
                    'type' => $this->input->post('type'),
                    'keyword' => $this->input->post('keyword'),
                    'is_active' => TRUE
                ]);

                if ($insert_id) {
                    $this->session->set_flashdata('success', 'Filter keyword created successfully.');
                    redirect('config/filter-keyword');
                    return;
                } else {
                    $this->session->set_flashdata('errors', '<p>Failed to create filter keyword. Please try again.</p>');
                }
            }
        }

        $data = [
            'title' => 'Create Filter Keyword',
            'active_menu' => 'configuration',
            'current_account' => [
                'name' => $this->session->userdata('name'),
                'role' => $this->session->userdata('role')
            ]
        ];

        $this->load->view('configuration/filter_keyword/create', $data);
    }

    public function edit($id)
    {
        // get keyword data
        $keyword = $this->Filter_keyword_model->get_by_id($id);
        if (!$keyword) {
            $this->session->set_flashdata('errors', '<p>Filter keyword not found.</p>');
            redirect('config/filter-keyword');
            return;
        }

        if ($this->input->method() === 'post') {
            // set validation rules
            $this->load->library('form_validation');
            $this->form_validation->set_rules([
                [
                    'field' => 'platform',
                    'label' => 'Platform',
                    'rules' => 'required|in_list[facebook,instagram,gam,ga4,youtube]'
                ],
                [
                    'field' => 'type',
                    'label' => 'Type',
                    'rules' => 'required|in_list[html,keyword,hostname]'
                ],
                [
                    'field' => 'keyword',
                    'label' => 'Keyword',
                    'rules' => 'required|trim|min_length[2]|max_length[255]'
                ],
                [
                    'field' => 'is_active',
                    'label' => 'Status',
                    'rules' => 'in_list[0,1]'
                ]
            ]);

            if ($this->form_validation->run() === TRUE) {
                $updated = $this->Filter_keyword_model->update($id, [
                    'platform'  => $this->input->post('platform'),
                    'type'      => $this->input->post('type'),
                    'keyword'   => $this->input->post('keyword'),
                    'is_active' => $this->input->post('is_active')
                ]);

                if ($updated) {
                    $this->session->set_flashdata('success', 'Filter keyword updated successfully.');
                    redirect('config/filter-keyword');
                    return;
                } else {
                    $this->session->set_flashdata('errors', '<p>Failed to update filter keyword. Please try again.</p>');
                }
            }
        }

        $data = [
            'title' => 'Edit Filter Keyword',
            'keyword' => $keyword,
            'active_menu' => 'configuration',
            'current_account' => [
                'name' => $this->session->userdata('name'),
                'role' => $this->session->userdata('role')
            ]
        ];

        $this->load->view('configuration/filter_keyword/edit', $data);
    }

    public function delete($id)
    {
        if ($this->input->method() !== 'post') {
            redirect('config/filter-keyword');
        }

        // check if keyword is exist
        if ($this->Filter_keyword_model->is_exist_by_id($id) == 0) {
            $this->session->set_flashdata('errors', '<p>Filter keyword not found.</p>');
            redirect('config/filter-keyword');
            return;
        }

        $deleted = $this->Filter_keyword_model->delete($id);

        if ($deleted) {
            $this->session->set_flashdata('success', 'Filter keyword deleted successfully.');
        } else {
            $this->session->set_flashdata('errors', '<p>Failed to delete filter keyword. Please try again.</p>');
        }

        redirect('config/filter-keyword');
    }
}
