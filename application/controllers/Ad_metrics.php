<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ad_metrics extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Ad_model');
        $this->load->model('Client_model');
        
        // Require login
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }

        // unauthorized if role is not superadmin
        if ($this->session->userdata('role') !== 'superadmin') {
            show_error('Unauthorized', 403);
        }
    }

    public function index()
    {
        $clients = $this->Ad_model->get_clients_summary();

        $data = [
            'title' => 'Ad Metrics',
            'clients' => $clients,
            'active_menu' => 'ad_metrics',
            'current_account' => [
                'name' => $this->session->userdata('name'),
                'role' => $this->session->userdata('role')
            ]
        ];

        $this->load->view('ad_metrics/index', $data);
    }

    public function detail($client_id)
    {
        $client_id = (int) $client_id;
        
        // get client from Client_model
        $client = $this->Client_model->get_by_id($client_id);
        
        if (!$client) {
            $this->session->set_flashdata('errors', 'Client not found.');
            redirect('ad-metrics');
            return;
        }

        // Get ad contents and metrics from Ad_metrics_model
        $ad_contents = $this->Ad_model->get_client_ad_metrics($client_id);

        $data = [
            'title' => 'Detail Metrik - ' . $client->company_name,
            'client' => $client,
            'ad_contents' => $ad_contents,
            'active_menu' => 'ad_metrics',
            'current_account' => [
                'name' => $this->session->userdata('name'),
                'role' => $this->session->userdata('role')
            ]
        ];

        $this->load->view('ad_metrics/detail', $data);
    }

    public function mapping()
    {
        if ($this->input->method() === 'post') {
            $selected_ids = $this->input->post('selected_ids') ?? [];
            $client_map = $this->input->post('client_id') ?? [];

            $saved = 0;
            foreach ($selected_ids as $ad_id) {
                $ad_id = (int) $ad_id;
                $client_id = (int) $client_map[$ad_id];

                $rows = $this->Ad_model->assign_client($ad_id, $client_id);
                $saved += $rows;
            }

            if ($saved > 0) {
                $this->session->set_flashdata('success', "{$saved} ads successfully mapped to client.");
            } else {
                $this->session->set_flashdata('errors', 'No ads was successfully saved. Please select a client for the checked rows.');
            }

            redirect('ad-metrics/mapping');
            return;
        }

        $this->load->model('Client_model');

        $data = [
            'title' => 'Ad Content Mapping',
            'unmapped' => $this->Ad_model->get_unmapped_contents(),
            'clients' => $this->Client_model->get_all(),
            'active_menu' => 'ad_metrics',
            'current_account' => [
                'name' => $this->session->userdata('name'),
                'role' => $this->session->userdata('role'),
            ],
        ];

        $this->load->view('ad_metrics/mapping', $data);
    }

    public function export_pdf($ad_content_id = 0)
    {
        $ad_content_id = (int) $ad_content_id;

        if ($ad_content_id <= 0) {
            show_error('Invalid ad content ID.', 400);
            return;
        }

        $ad = $this->Ad_model->get_ad_with_metrics($ad_content_id);

        if (!$ad) {
            show_error('Ad content not found.', 404);
            return;
        }

        // render the html template to a string
        $html = $this->load->view('templates/pdf', ['ad' => $ad], TRUE);

        // load library and stream pdf
        $this->load->library('Export_pdf');
        $filename = 'ad_report_' . $ad_content_id . '_' . date('Ymd');
        $this->export_pdf->generate($html, $filename);
    }

    public function export_excel($ad_content_id = 0)
    {
        $ad_content_id = (int) $ad_content_id;

        if ($ad_content_id <= 0) {
            show_error('Invalid ad content ID.', 400);
            return;
        }

        $ad = $this->Ad_model->get_ad_with_metrics($ad_content_id);

        if (!$ad) {
            show_error('Ad content not found.', 404);
            return;
        }

        // load library and stream excel file
        $this->load->library('Export_excel');
        $filename = 'ad_report_' . $ad_content_id . '_' . date('Ymd');
        $this->export_excel->generate($ad, $filename);
    }
}

