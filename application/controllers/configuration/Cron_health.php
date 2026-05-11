<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron_health extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Cron_health_model');

        // require login
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }

        // superadmin only
        if ($this->session->userdata('role') !== 'superadmin') {
            redirect('welcome');
        }
    }

    public function index()
    {
        $data = [
            'title'           => 'Cron Health',
            'active_menu'     => 'cron_health',
            'current_account' => [
                'name' => $this->session->userdata('name'),
                'role' => $this->session->userdata('role'),
            ],
            'latest'          => $this->Cron_health_model->get_latest_per_cron(),
        ];

        $this->load->view('configuration/cron_health/index', $data);
    }

    /**
     * Get history for a specific cron job.
     * GET /config/cron-health/history/:cron_name (returns JSON)
     */
    public function history($cron_name = '')
    {
        header('Content-Type: application/json');

        $cron_name = urldecode($cron_name);
        if (empty($cron_name)) {
            echo json_encode(['success' => false, 'message' => 'cron_name diperlukan.']);
            return;
        }

        $history = $this->Cron_health_model->get_history($cron_name, 10);
        echo json_encode(['success' => true, 'data' => $history]);
    }
}
