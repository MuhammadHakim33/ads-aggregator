<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Client_model');
        $this->load->model('Contract_model');
        $this->load->model('Campaign_model');
        $this->load->model('Ad_model');
        $this->load->model('Cron_log_model');
    }

    public function index()
    {
        $data = [
            'title' => 'Dashboard',
            'active_menu' => 'dashboard',
            'total_clients_active' => $this->Client_model->count_active(),
            'total_clients' => $this->Client_model->count_total(),
            'total_contracts_active' => $this->Contract_model->count_active(),
            'total_campaigns_running' => $this->Campaign_model->count_running(),
            'total_ads' => $this->Ad_model->count_all_ads(),
            'total_unconnected_ads' => $this->Ad_model->count_unconnected(),
            'cron_last_per_platform' => $this->Cron_log_model->get_last_per_platform(),
            'cron_recent' => $this->Cron_log_model->get_latest(10),
        ];

        $this->render('dashboard', $data);
    }
}
