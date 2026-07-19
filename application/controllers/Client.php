<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Client extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->require_role('manajemen');
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
            $rules = [
                [
                    'field' => 'company_name',
                    'label' => 'Company Name',
                    'rules' => 'trim|required|max_length[255]'
                ],
                [
                    'field' => 'ae_id',
                    'label' => 'Account Executive',
                    'rules' => 'trim|callback_ae_check'
                ]
            ];

            $this->form_validation->set_rules($rules);

            if ($this->form_validation->run() === TRUE) {
                $client_data = [
                    'company_name' => $this->input->post('company_name'),
                    'ae_id' => $this->input->post('ae_id') ?: null,
                    'is_active' => TRUE
                ];

                $client_id = $this->Client_model->insert($client_data);

                if ($client_id) {
                    $this->session->set_flashdata('success', 'Client created successfully. Please manage PICs for this client from the client list.');
                    redirect('client');
                    return;
                } else {
                    $this->session->set_flashdata('errors', 'Failed to create client. Please try again.');
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
        $client = $this->Client_model->get_by_id($id);
        if (!$client) {
            $this->session->set_flashdata('errors', 'Client not found.');
            redirect('client');
            return;
        }

        if ($this->input->method() === 'post') {
            $rules = [
                [
                    'field' => 'company_name',
                    'label' => 'Company Name',
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
            ];

            $this->form_validation->set_rules($rules);

            if ($this->form_validation->run() === TRUE) {
                $client_data = [
                    'company_name' => $this->input->post('company_name'),
                    'ae_id' => $this->input->post('ae_id') ?: null,
                    'is_active' => $this->input->post('is_active'),
                ];

                $this->Client_model->update($id, $client_data);

                $this->session->set_flashdata('success', 'Client updated successfully.');
                redirect('client');
                return;
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

        $client = $this->Client_model->get_by_id($id);
        if (!$client) {
            $this->session->set_flashdata('errors', 'Client not found.');
            redirect('client');
            return;
        }

        $this->db->trans_start();

        // get all PICs and deactivate their accounts + set PICs to inactive
        $pics = $this->db->where('client_id', $id)->get('client_pics')->result();
        foreach ($pics as $pic) {
            if (!empty($pic->account_id)) {
                $this->db->where('id', $pic->account_id)->update('accounts', ['is_active' => 0]);
            }
            $this->db->where('id', $pic->id)->update('client_pics', ['is_active' => 0]);
        }

        $this->Client_model->delete($id);

        $this->db->trans_complete();

        if ($this->db->trans_status() === TRUE) {
            $this->session->set_flashdata('success', 'Client and all associated PIC accounts deactivated successfully.');
        } else {
            $this->session->set_flashdata('errors', 'Failed to delete client. Please try again.');
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
