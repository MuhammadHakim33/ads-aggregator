<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        // check if user already logged in
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
        $this->load->model('Client_model');
        $this->load->model('Ad_content_model');

        $data = [
            'title' => 'Dashboard',
            'active_menu' => 'dashboard',
            'current_account' => [
                'name' => $this->session->userdata('name'),
                'role' => $this->session->userdata('role'),
            ],
            'total_active_clients' => $this->Client_model->count_active(),
            'total_active_ads' => $this->Ad_content_model->count_active(),
        ];

        $this->load->view('dashboard', $data);
    }
}
