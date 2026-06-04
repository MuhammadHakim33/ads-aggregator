<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Role extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Role_model');

        $this->require_superadmin();
    }

    public function index()
    {
        $roles = $this->Role_model->get_all();

        $data = [
            'title' => 'Role',
            'active_menu' => 'role',
            'roles' => $roles,
        ];

        $this->render('configuration/roles/index', $data);
    }

    public function create()
    {
        if ($this->input->method() === 'post') {

            $this->form_validation->set_rules('name', 'Name', 'required|is_unique[roles.name]');
            if ($this->form_validation->run() === TRUE) {
                $insert_id = $this->Role_model->insert([
                    'name' => $this->input->post('name'),
                ]);

                if (empty($insert_id)) {
                    $this->session->set_flashdata('errors', 'Failed to create role. Please try again.');
                    redirect('config/role');
                    return;
                }

                $this->session->set_flashdata('success', 'Role created successfully.');
                redirect('config/role');
                return;
            }
        }

        $data = [
            'title' => 'Create Role',
            'active_menu' => 'role',
        ];

        $this->render('configuration/roles/create', $data);
        return;
    }

    public function edit($id)
    {
        $role = $this->Role_model->get_by_id($id);
        if (empty($role)) {
            $this->session->set_flashdata('errors', '<p>Role not found.</p>');
            redirect('config/role');
            return;
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('name', 'Name', 'required|is_unique[roles.name]');
            if ($this->form_validation->run() === TRUE) {
                $update = $this->Role_model->update($id, [
                    'name' => $this->input->post('name'),
                ]);

                if ($update <= 0) {
                    $this->session->set_flashdata('failed', 'Failed to update role. Please try again.');
                    redirect('config/role');
                    return;
                }

                $this->session->set_flashdata('success', 'Role updated successfully.');
                redirect('config/role');
                return;
            }
        }

        $data = [
            'title' => 'Edit Role',
            'active_menu' => 'role',
            'role' => $role,
        ];

        $this->render('configuration/roles/edit', $data);
        return;
    }

    public function delete($id)
    {
        if ($this->input->method() !== 'post') {
            redirect('config/role');
        }

        if ($this->Role_model->is_exist_by_id($id) == 0) {
            $this->session->set_flashdata('errors', '<p>Role not found.</p>');
            redirect('config/role');
            return;
        }

        $deleted = $this->Role_model->delete($id);

        if ($deleted) {
            $this->session->set_flashdata('success', 'Role deleted successfully.');
        } else {
            $this->session->set_flashdata('errors', '<p>Failed to delete role. Please try again.</p>');
        }

        redirect('config/role');
    }
}