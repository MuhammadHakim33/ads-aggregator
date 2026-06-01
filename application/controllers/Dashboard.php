<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Client_model');
        $this->load->model('Ad_model');
    }

    public function index()
    {
        $data = [
            'title' => 'Dashboard',
            'active_menu' => 'dashboard',
            'total_active_clients' => $this->Client_model->count_active(),
            'total_active_ads' => $this->Ad_model->count_active(),
        ];

        $this->render('dashboard', $data);
    }
}
