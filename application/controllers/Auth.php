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

    public function login()
    {
        $this->form_validation->set_rules([
            ['field' => 'email',    'label' => 'Email',    'rules' => 'required|valid_email'],
            ['field' => 'password', 'label' => 'Password', 'rules' => 'required'],
        ]);

        if ($this->form_validation->run() === FALSE) {
            return $this->_json(400, ['status' => 'error', 'message' => validation_errors()]);
        }

        $account = $this->Auth_model->find_by_email($this->input->post('email'));

        if (!$account || !password_verify($this->input->post('password'), $account->password)) {
            return $this->_json(401, ['status' => 'error', 'message' => 'Invalid email or password']);
        }

        $this->session->set_userdata([
            'id'        => $account->id,
            'name'      => $account->name,
            'email'     => $account->email,
            'role'      => $account->role,
            'logged_in' => TRUE
        ]);

        return $this->_json(200, [
            'status' => 'success',
            'data'   => [
                'name'  => $account->name,
                'role'  => $account->role,
            ],
        ]);
    }

    public function logout()
    {
        if ($this->input->method() !== 'post') {
            return $this->_json(405, ['status' => 'error', 'message' => 'Method Not Allowed']);
        }

        $this->session->sess_destroy();

        return $this->_json(200, ['status' => 'success', 'message' => 'Logged out successfully']);
    }

    private function _json($status_code, $data)
    {
        $this->output
            ->set_status_header($status_code)
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }
}
