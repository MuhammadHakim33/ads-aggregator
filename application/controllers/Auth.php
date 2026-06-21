<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Account_model');
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
            ['field' => 'email', 'label' => 'Email', 'rules' => 'required|valid_email'],
            ['field' => 'password', 'label' => 'Password', 'rules' => 'required'],
        ]);

        // validate form
        if ($this->form_validation->run() === FALSE) {
            $data['title'] = 'Login';
            $this->load->view('auth/login', $data);
            return;
        }

        $account = $this->Account_model->get_by_email($this->input->post('email'));

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
            'role' => $account->role_name,
            'logged_in' => TRUE
        ]);

        redirect('dashboard');
    }

    public function logout()
    {
        $this->session->sess_destroy();
        redirect('auth');
    }

    public function forgot_password()
    {
        if ($this->session->userdata('logged_in')) {
            redirect('dashboard');
        }

        $data['title'] = 'Forgot Password';
        $this->load->view('auth/forgot_password', $data);
    }

    public function send_reset_link()
    {
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');

        if ($this->form_validation->run() === FALSE) {
            $this->forgot_password();
            return;
        }

        $email = $this->input->post('email');
        $account = $this->Account_model->get_by_email($email);

        if ($account) {
            $token = bin2hex(random_bytes(32));
            $expired_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $this->Account_model->set_reset_token($email, $token, $expired_at);

            $reset_link = base_url('auth/reset_password/' . $token);

            $subject = 'Password Reset Request - Kontan Ad Reporter';

            // pass the reset link to the view
            $email_data['reset_link'] = $reset_link;

            // load the HTML view into a variable
            $message = $this->load->view('email/reset_password', $email_data, TRUE);

            $this->load->helper('common');
            send_email($email, $subject, $message);
        }

        $this->session->set_flashdata('success', 'If the email is registered, a reset link will be sent shortly.');
        redirect('auth/forgot_password');
    }

    public function reset_password($token = null)
    {
        if (!$token) {
            redirect('auth');
        }

        $account = $this->Account_model->get_by_reset_token($token);

        if (!$account) {
            $this->session->set_flashdata('error', 'Invalid or expired password reset link.');
            redirect('auth/forgot_password');
        }

        $data['title'] = 'Reset Password';
        $data['token'] = $token;
        $this->load->view('auth/reset_password', $data);
    }

    public function update_password()
    {
        $token = $this->input->post('token');
        if (!$token) {
            redirect('auth');
        }

        $account = $this->Account_model->get_by_reset_token($token);

        if (!$account) {
            $this->session->set_flashdata('error', 'Invalid or expired password reset link.');
            redirect('auth/forgot_password');
        }

        $this->form_validation->set_rules('password', 'New Password', 'required|min_length[6]');
        $this->form_validation->set_rules('password_confirm', 'Confirm Password', 'required|matches[password]');

        if ($this->form_validation->run() === FALSE) {
            $data['title'] = 'Reset Password';
            $data['token'] = $token;
            $this->load->view('auth/reset_password', $data);
            return;
        }

        $new_password = $this->input->post('password');

        $this->Account_model->update($account->id, ['password' => $new_password]);
        $this->Account_model->clear_reset_token($account->id);

        $this->session->set_flashdata('success', 'Password has been successfully updated. You can now login.');
        redirect('auth');
    }
}
