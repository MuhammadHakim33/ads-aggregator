<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Account extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Account_model');
    }

    public function index()
    {
        $accounts = $this->Account_model->get_all();

        foreach ($accounts as &$acc) {
            unset($acc->password);
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'data' => $accounts
            ]));
    }

    public function create()
    {
        if ($this->input->method() !== 'post') {
            return $this->output
                ->set_status_header(405)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Method Not Allowed. Please use POST.']));
        }

        $this->form_validation->set_rules($this->_create_rules());
        if ($this->form_validation->run() === FALSE) {
            return $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => validation_errors()]));
        }

        $insert_id = $this->Account_model->insert([
            'name'     => $this->input->post('name'),
            'email'    => $this->input->post('email'),
            'password' => $this->input->post('password'),
            'role'     => $this->input->post('role'),
            'is_active' => TRUE
        ]);

        if ($insert_id) {
            $this->output
                ->set_status_header(201)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'success',
                    'message' => 'Account created successfully',
                    'data' => ['id' => $insert_id]
                ]));
        } else {
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Failed to create account']));
        }
    }

    public function update($id)
    {
        if ($this->input->method() !== 'post') {
            return $this->output
                ->set_status_header(405)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Method Not Allowed. Please use POST.']));
        }

        if (!$id) {
            return $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Account ID is required']));
        }

        if ($this->Account_model->is_exist_by_id($id) == 0) {
            return $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Account not found']));
        }

        $this->form_validation->set_rules($this->_update_rules($id));
        if ($this->form_validation->run() === FALSE) {
            return $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => validation_errors()]));
        }

        $data = [];

        $name = $this->input->post('name');
        if ($name !== null) $data['name'] = $name;

        $email = $this->input->post('email');
        if ($email !== null) $data['email'] = $email;

        $password = $this->input->post('password');
        if ($password !== null && $password !== '') $data['password'] = $password;

        $role = $this->input->post('role');
        if ($role !== null) $data['role'] = $role;

        $is_active = $this->input->post('is_active');
        if ($is_active !== null) $data['is_active'] = $is_active;

        if (empty($data)) {
            return $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'No data to update']));
        }

        $updated = $this->Account_model->update($id, $data);

        if ($updated) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'success', 'message' => 'Account updated successfully']));
        } else {
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Failed to update account']));
        }
    }

    public function delete($id)
    {
        if ($this->input->method() !== 'delete') {
            return $this->output
                ->set_status_header(405)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Method Not Allowed. Please use DELETE.']));
        }

        if (!$id) {
            return $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Account ID is required']));
        }

        if ($this->Account_model->is_exist_by_id($id) == 0) {
            return $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Account not found']));
        }

        $deleted = $this->Account_model->delete($id);

        if ($deleted) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'success', 'message' => 'Account deleted successfully']));
        } else {
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Failed to delete account']));
        }
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
