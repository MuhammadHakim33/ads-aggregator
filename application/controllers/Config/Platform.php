<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Platform extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Platform_model');

        $this->require_superadmin();
    }

    public function index()
    {
        $platforms = $this->Platform_model->get_all();

        $data = [
            'title' => 'Platform',
            'active_menu' => 'platform',
            'platforms' => $platforms,
        ];

        $this->render('configuration/platforms/index', $data);
    }

    public function create()
    {
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('name', 'Name', 'required|is_unique[platforms.name]');
            if ($this->form_validation->run() === TRUE) {
                $insert_id = $this->Platform_model->insert([
                    'name' => $this->input->post('name'),
                ]);

                if (empty($insert_id)) {
                    $this->session->set_flashdata('errors', 'Failed to create platform. Please try again.');
                    redirect('config/platform');
                    return;
                }

                $this->session->set_flashdata('success', 'Platform created successfully.');
                redirect('config/platform');
                return;
            }
        }

        $data = [
            'title' => 'Create Platform',
            'active_menu' => 'platform',
        ];

        $this->render('configuration/platforms/create', $data);
        return;
    }

    public function edit($id)
    {
        $platform = $this->Platform_model->get_by_id($id);
        if (empty($platform)) {
            $this->session->set_flashdata('errors', '<p>Platform not found.</p>');
            redirect('config/platform');
            return;
        }

        if ($this->input->method() === 'post') {
            $old_name = $platform->name;
            $new_name = $this->input->post('name');
            $is_unique = ($new_name === $old_name) ? '' : '|is_unique[platforms.name]';
            
            $this->form_validation->set_rules('name', 'Name', 'required' . $is_unique);
            if ($this->form_validation->run() === TRUE) {
                $update = $this->Platform_model->update($id, [
                    'name' => $new_name,
                ]);

                if ($update <= 0) {
                    $this->session->set_flashdata('errors', 'Failed to update platform or name unchanged.');
                    redirect('config/platform');
                    return;
                }

                $this->session->set_flashdata('success', 'Platform updated successfully.');
                redirect('config/platform');
                return;
            }
        }

        $data = [
            'title' => 'Edit Platform',
            'active_menu' => 'platform',
            'platform' => $platform,
        ];

        $this->render('configuration/platforms/edit', $data);
        return;
    }

    public function delete($id)
    {
        if ($this->input->method() !== 'post') {
            redirect('config/platform');
        }

        $platform = $this->Platform_model->get_by_id($id);
        if (empty($platform)) {
            $this->session->set_flashdata('errors', '<p>Platform not found.</p>');
            redirect('config/platform');
            return;
        }

        $deleted = $this->Platform_model->delete($id);

        if ($deleted) {
            $this->session->set_flashdata('success', 'Platform deleted successfully.');
        } else {
            $this->session->set_flashdata('errors', '<p>Failed to delete platform. Please try again.</p>');
        }

        redirect('config/platform');
    }
}
