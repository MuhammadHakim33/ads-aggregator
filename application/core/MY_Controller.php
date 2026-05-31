<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
    protected $current_account = [];

    public function __construct()
    {
        parent::__construct();

        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }

        $this->current_account = [
            'name' => $this->session->userdata('name'),
            'role' => $this->session->userdata('role'),
        ];
    }

    protected function require_superadmin()
    {
        if ($this->current_account['role'] !== 'superadmin') {
            show_error('Unauthorized', 403);
        }
    }

    protected function render($view, $data = [])
    {
        $data['current_account'] = $this->current_account;
        $this->load->view($view, $data);
    }
}
