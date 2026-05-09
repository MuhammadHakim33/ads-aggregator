<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ad_metrics extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Ad_metrics_model');
        $this->load->model('Client_model');
        
        // Require login
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }

        if ($this->session->userdata('role') !== 'superadmin') {
            redirect('welcome');
        }
    }

    public function index()
    {
        $clients = $this->Ad_metrics_model->get_clients_summary();

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
        
        // Get client from Client_model
        $client = $this->Client_model->get_by_id($client_id);
        
        if (!$client) {
            $this->session->set_flashdata('errors', 'Klien tidak ditemukan.');
            redirect('ad-metrics');
            return;
        }

        // Get ad contents and metrics from Ad_metrics_model
        $ad_contents = $this->Ad_metrics_model->get_client_ad_metrics($client_id);

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
            $client_map   = $this->input->post('client_id') ?? [];

            $saved = 0;
            $skipped = 0;

            foreach ($selected_ids as $ad_id) {
                $ad_id     = (int) $ad_id;
                $client_id = isset($client_map[$ad_id]) ? (int) $client_map[$ad_id] : 0;

                if ($client_id <= 0) {
                    $skipped++;
                    continue;
                }

                $rows = $this->Ad_metrics_model->assign_client($ad_id, $client_id);
                $saved += $rows;
            }

            if ($saved > 0) {
                $this->session->set_flashdata('success', "{$saved} ad content berhasil di-mapping ke klien." . ($skipped > 0 ? " {$skipped} baris dilewati (klien tidak dipilih)." : ''));
            } else {
                $this->session->set_flashdata('errors', 'Tidak ada data yang berhasil disimpan. Pastikan klien dipilih untuk baris yang dicentang.');
            }

            redirect('ad-metrics/mapping');
            return;
        }

        // GET: load unmapped contents and all clients
        $this->load->model('Client_model');

        $data = [
            'title'           => 'Ad Content Mapping',
            'unmapped'        => $this->Ad_metrics_model->get_unmapped_contents(),
            'clients'         => $this->Client_model->get_all(),
            'active_menu'     => 'ad_metrics',
            'current_account' => [
                'name' => $this->session->userdata('name'),
                'role' => $this->session->userdata('role'),
            ],
        ];

        $this->load->view('ad_metrics/mapping', $data);
    }
}
