<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Complaint extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->require_role('client', 'ae', 'manajemen');
        $this->load->model('Complaint_model');
        $this->load->model('Client_model');
        $this->load->model('Ad_model');
        $this->load->library('form_validation');
    }

    public function index()
    {
        $role = $this->current_account['role'];
        $user_id = $this->current_account['id'];
        $filters = [
            'q' => $this->input->get('q'),
            'status' => $this->input->get('status')
        ];

        if ($role === 'client') {
            $client = $this->Client_model->get_by_account_id($user_id);
            $filters['client_id'] = $client ? $client->id : -1;
        } elseif ($role === 'ae') {
            $filters['ae_id'] = $user_id;
        }

        $data = [
            'title' => 'Complaints',
            'active_menu' => 'complaint',
            'filters' => $filters,
            'complaints' => $this->Complaint_model->get_all($filters)
        ];

        $this->render('complaint/index', $data);
    }

    public function create()
    {
        $this->require_role('client');
        $user_id = $this->current_account['id'];

        $client = $this->Client_model->get_by_account_id($user_id);
        $client_id = $client ? $client->id : -1;

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('subject', 'Subject', 'trim|required|max_length[255]');
            $this->form_validation->set_rules('ad_content_id', 'Ad Content', 'required|integer|callback_ad_ownership_check[' . $client_id . ']');
            $this->form_validation->set_rules('description', 'Description', 'trim|required');

            if ($this->form_validation->run() === TRUE) {
                $insert_id = $this->Complaint_model->insert([
                    'ad_content_id' => $this->input->post('ad_content_id'),
                    'subject' => $this->input->post('subject'),
                    'description' => $this->input->post('description'),
                    'status' => 'waiting'
                ]);

                if ($insert_id) {
                    $this->session->set_flashdata('success', 'Complaint submitted successfully.');
                    redirect('complaint');
                    return;
                } else {
                    $this->session->set_flashdata('errors', '<p>Failed to submit complaint. Please try again.</p>');
                }
            }
        }

        $data = [
            'title' => 'Create Complaint',
            'active_menu' => 'complaint',
            'ads' => $this->Ad_model->get_ads_by_client($client_id)
        ];

        $this->render('complaint/create', $data);
    }

    public function detail($id)
    {
        $complaint = $this->Complaint_model->get_by_id($id);
        if (!$complaint) {
            show_404();
            return;
        }

        // Authorization checks
        $role = $this->current_account['role'];
        $user_id = $this->current_account['id'];

        if ($role === 'client') {
            $client = $this->Client_model->get_by_account_id($user_id);
            $client_id = $client ? $client->id : -1;
            if ($complaint->client_id != $client_id) {
                show_error('Unauthorized', 403);
                return;
            }
        } elseif ($role === 'ae') {
            if ($complaint->ae_id != $user_id) {
                show_error('Unauthorized', 403);
                return;
            }
        }

        $data = [
            'title' => 'Complaint Detail',
            'active_menu' => 'complaint',
            'complaint' => $complaint
        ];

        $this->render('complaint/detail', $data);
    }

    public function update_status($id)
    {
        $this->require_role('ae');
        $complaint = $this->Complaint_model->get_by_id($id);
        if (!$complaint) {
            show_404();
            return;
        }

        // authorization check for associated AE
        $user_id = $this->current_account['id'];
        if ($complaint->ae_id != $user_id) {
            show_error('Unauthorized', 403);
            return;
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('status', 'Status', 'required|in_list[waiting,in_progress,resolved,closed]');
            $this->form_validation->set_rules('resolution_note', 'Resolution Note', 'trim');

            if ($this->form_validation->run() === TRUE) {
                $this->Complaint_model->update($id, [
                    'status' => $this->input->post('status'),
                    'resolution_note' => $this->input->post('resolution_note')
                ]);
                $this->session->set_flashdata('success', 'Complaint status updated successfully.');
            } else {
                $this->session->set_flashdata('errors', validation_errors());
            }
        }

        redirect('complaint/detail/' . $id);
    }

    // Callback validation to check if the client owns the selected ad
    public function ad_ownership_check($ad_content_id, $client_id)
    {
        $ad = $this->Ad_model->get_ad_with_metrics($ad_content_id);
        if (!$ad || $ad->client_id != $client_id) {
            $this->form_validation->set_message([
                'ad_ownership_check' => 'Selected ad is invalid or does not belong to you.'
            ]);
            return FALSE;
        }
        return TRUE;
    }
}
