<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Account extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Account_model');

        // require login
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
        $data['accounts'] = $this->Account_model->get_all();
        $data['current_account'] = [
            'name' => $this->session->userdata('name'),
            'role' => $this->session->userdata('role')
        ];
        $data['title'] = 'Account';
        $data['active_menu'] = 'account';

        $this->load->view('account/index', $data);
    }

    public function create()
    {
        // check if method is post
        if ($this->input->method() === 'post') {
            // validate form
            $this->load->library('form_validation');
            $this->form_validation->set_rules($this->_create_rules());

            if ($this->form_validation->run() === TRUE) {
                $insert_id = $this->Account_model->insert([
                    'name' => $this->input->post('name'),
                    'email' => $this->input->post('email'),
                    'password' => $this->input->post('password'),
                    'role' => $this->input->post('role'),
                    'is_active' => TRUE
                ]);

                if ($insert_id) {
                    $this->session->set_flashdata('success', 'Account created successfully.');
                    redirect('account');
                    return;
                } else {
                    $this->session->set_flashdata('errors', '<p>Failed to create account. Please try again.</p>');
                }
            }
        }

        $data['title'] = 'Create Account';
        $data['active_menu'] = 'account';
        $data['current_account'] = [
            'name' => $this->session->userdata('name'),
            'role' => $this->session->userdata('role')
        ];

        $this->load->view('account/create', $data);
    }

    public function edit($id)
    {
        // check account exists
        $account = $this->Account_model->get_by_id($id);
        if (!$account) {
            $this->session->set_flashdata('errors', '<p>Account not found.</p>');
            redirect('account');
        }

        // check if method is post
        if ($this->input->method() === 'post') {
            // validate form
            $this->load->library('form_validation');
            $this->form_validation->set_rules($this->_update_rules($id));

            if ($this->form_validation->run() === TRUE) {
                $data = [
                    'name' => $this->input->post('name'),
                    'email' => $this->input->post('email'),
                    'role' => $this->input->post('role'),
                    'is_active' => $this->input->post('is_active'),
                ];

                // only update if password is not empty
                $password = $this->input->post('password');
                if ($password !== null && $password !== '') {
                    $data['password'] = $password;
                }

                // update account
                $updated = $this->Account_model->update($id, $data);

                if ($updated) {
                    $this->session->set_flashdata('success', 'Account updated successfully.');
                    redirect('account');
                    return;
                } else {
                    $this->session->set_flashdata('errors', '<p>Failed to update account. Please try again.</p>');
                }
            }
        }

        $data['account'] = $account;
        $data['title'] = 'Edit Account';
        $data['active_menu'] = 'account';
        $data['current_account'] = [
            'name' => $this->session->userdata('name'),
            'role' => $this->session->userdata('role')
        ];

        $this->load->view('account/edit', $data);
    }


    public function delete($id)
    {
        // check if method is post
        if ($this->input->method() !== 'post') {
            redirect('account');
        }

        // check account exists
        $account = $this->Account_model->get_by_id($id);
        if (!$account) {
            $this->session->set_flashdata('errors', '<p>Account not found.</p>');
            redirect('account');
            return;
        }

        // prevent superadmin from deleting their own account
        if ((int)$id === (int)$this->session->userdata('id')) {
            $this->session->set_flashdata('errors', '<p>You cannot delete your own account.</p>');
            redirect('account');
            return;
        }

        $deleted = $this->Account_model->delete($id);

        if ($deleted) {
            $this->session->set_flashdata('success', 'Account deleted successfully.');
        } else {
            $this->session->set_flashdata('errors', '<p>Failed to delete account. Please try again.</p>');
        }

        redirect('account');
    }

    private function _create_rules()
    {
        return [
            [
                'field' => 'name',
                'label' => 'Name',
                'rules' => 'trim|required|min_length[3]|max_length[50]'
            ],
            [
                'field' => 'email',
                'label' => 'Email',
                'rules' => 'trim|required|valid_email|is_unique[accounts.email]'
            ],
            [
                'field' => 'password',
                'label' => 'Password',
                'rules' => 'trim|required|min_length[6]'
            ],
            [
                'field' => 'role',
                'label' => 'Role',
                'rules' => 'trim|in_list[ae,superadmin]'
            ]
        ];
    }

    private function _update_rules($id)
    {
        return [
            [
                'field' => 'name',
                'label' => 'Name',
                'rules' => 'trim|min_length[3]|max_length[50]'
            ],
            [
                'field' => 'email',
                'label' => 'Email',
                'rules' => 'trim|valid_email|callback_email_check[' . $id . ']'
            ],
            [
                'field' => 'password',
                'label' => 'Password',
                'rules' => 'trim|min_length[6]'
            ],
            [
                'field' => 'role',
                'label' => 'Role',
                'rules' => 'trim|in_list[ae,superadmin]'
            ],
            [
                'field' => 'is_active',
                'label' => 'Is Active',
                'rules' => 'in_list[0,1]'
            ]
        ];
    }

    public function email_check($email, $id)
    {
        if ($this->Account_model->is_email_used($email, $id) > 0) {
            $this->form_validation->set_message(
                'email_check',
                'Email has already been used'
            );
            return FALSE;
        }

        return TRUE;
    }
}
