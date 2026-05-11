<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Auth_model');
        $this->load->library('session');
    }

    public function index()
    {
        // check if user is already logged in
        if ($this->session->userdata('logged_in')) {
            redirect('welcome');
        }

        $data['title'] = 'Login';
        $this->load->view('auth/login', $data);
    }

    public function login()
    {
        // set validation rules
        $this->form_validation->set_rules([
            ['field' => 'email',    'label' => 'Email',    'rules' => 'required|valid_email'],
            ['field' => 'password', 'label' => 'Password', 'rules' => 'required'],
        ]);

        // validate form
        if ($this->form_validation->run() === FALSE) {
            $data['title'] = 'Login';
            $this->load->view('auth/login', $data);
            return;
        }

        $account = $this->Auth_model->find_by_email($this->input->post('email'));

        // check if account exists
        if (!$account || !password_verify($this->input->post('password'), $account->password)) {
            $this->session->set_flashdata('login-failed', 'Invalid email or password');
            redirect('auth');
            return;
        }

        $this->session->set_userdata([
            'id' => $account->id,
            'name' => $account->name,
            'email' => $account->email,
            'role' => $account->role,
            'logged_in' => TRUE
        ]);

        redirect('welcome');
    }

    public function logout()
    {
        $this->session->sess_destroy();
        redirect('auth');
    }
}
