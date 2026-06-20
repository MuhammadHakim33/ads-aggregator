<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Client extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Client_model');
        $this->load->model('Account_model');
    }

    public function index()
    {
        $filters = [
            'q' => $this->input->get('q'),
            'status' => $this->input->get('status')
        ];

        $data = [
            'title' => 'Client',
            'active_menu' => 'client',
            'filters' => $filters,
            'clients' => $this->Client_model->get_all($filters)
        ];

        $this->render('client/index', $data);
    }

    public function create()
    {
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules([
                [
                    'field' => 'company_name',
                    'label' => 'Company Name',
                    'rules' => 'trim|required|max_length[255]'
                ],
                [
                    'field' => 'pic_name',
                    'label' => 'PIC Name',
                    'rules' => 'trim|required|max_length[255]'
                ],
                [
                    'field' => 'ae_id',
                    'label' => 'Account Executive',
                    'rules' => 'trim|callback_ae_check'
                ]
            ]);

            if ($this->form_validation->run() === TRUE) {
                $insert_id = $this->Client_model->insert([
                    'company_name' => $this->input->post('company_name'),
                    'pic_name' => $this->input->post('pic_name'),
                    'ae_id' => $this->input->post('ae_id') ?: null,
                    'is_active' => TRUE
                ]);

                if ($insert_id) {
                    $this->session->set_flashdata('success', 'Client created successfully.');
                    redirect('client');
                    return;
                } else {
                    $this->session->set_flashdata('errors', '<p>Failed to create client. Please try again.</p>');
                }
            }
        }

        $data = [
            'title' => 'Create Client',
            'active_menu' => 'client',
            'ae_list' => $this->Account_model->get_all_ae()
        ];

        $this->render('client/create', $data);
    }

    public function edit($id)
    {
        // Load client data
        $client = $this->Client_model->get_by_id($id);
        if (!$client) {
            $this->session->set_flashdata('errors', '<p>Client not found.</p>');
            redirect('client');
            return;
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules([
                [
                    'field' => 'company_name',
                    'label' => 'Company Name',
                    'rules' => 'trim|required|max_length[255]'
                ],
                [
                    'field' => 'pic_name',
                    'label' => 'PIC Name',
                    'rules' => 'trim|required|max_length[255]'
                ],
                [
                    'field' => 'ae_id',
                    'label' => 'Account Executive',
                    'rules' => 'trim|callback_ae_check'
                ],
                [
                    'field' => 'is_active',
                    'label' => 'Status',
                    'rules' => 'in_list[0,1]'
                ]
            ]);

            if ($this->form_validation->run() === TRUE) {
                $updated = $this->Client_model->update($id, [
                    'company_name' => $this->input->post('company_name'),
                    'pic_name' => $this->input->post('pic_name'),
                    'ae_id' => $this->input->post('ae_id') ?: null,
                    'is_active' => $this->input->post('is_active'),
                ]);

                if ($updated) {
                    $this->session->set_flashdata('success', 'Client updated successfully.');
                    redirect('client');
                    return;
                } else {
                    $this->session->set_flashdata('errors', '<p>Failed to update client. Please try again.</p>');
                }
            }
        }

        $data = [
            'title' => 'Edit Client',
            'client' => $client,
            'active_menu' => 'client',
            'ae_list' => $this->Account_model->get_all_ae()
        ];

        $this->render('client/edit', $data);
    }

    public function delete($id)
    {
        if ($this->input->method() !== 'post') {
            redirect('client');
        }

        // load client data using callback method
        $client = $this->Client_model->get_by_id($id);
        if (!$client) {
            $this->session->set_flashdata('errors', '<p>Client not found.</p>');
            redirect('client');
            return;
        }

        $deleted = $this->Client_model->delete($id);

        if ($deleted) {
            $this->session->set_flashdata('success', 'Client deleted successfully.');
        } else {
            $this->session->set_flashdata('errors', '<p>Failed to delete client. Please try again.</p>');
        }

        redirect('client');
    }

    public function ae_check($ae_id)
    {
        if (empty($ae_id)) {
            return TRUE;
        }

        if ($this->Account_model->is_ae_exist_by_id($ae_id) == 0) {
            $this->form_validation->set_message([
                'ae_check' => 'The selected Account Executive does not exist or is inactive'
            ]);
            return FALSE;
        }

        return TRUE;
    }
}
