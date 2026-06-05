<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Keyword_type extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Keyword_type_model');

        $this->require_superadmin();
    }

    public function index()
    {
        $keyword_types = $this->Keyword_type_model->get_all();

        $data = [
            'title' => 'Keyword Type',
            'active_menu' => 'keyword_type',
            'keyword_types' => $keyword_types,
        ];

        $this->render('configuration/keyword_types/index', $data);
    }

    public function create()
    {
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('name', 'Name', 'required|is_unique[keyword_types.name]');
            if ($this->form_validation->run() === TRUE) {
                $insert_id = $this->Keyword_type_model->insert([
                    'name' => $this->input->post('name'),
                ]);

                if (empty($insert_id)) {
                    $this->session->set_flashdata('errors', 'Failed to create keyword type. Please try again.');
                    redirect('config/keyword-type');
                    return;
                }

                $this->session->set_flashdata('success', 'Keyword type created successfully.');
                redirect('config/keyword-type');
                return;
            }
        }

        $data = [
            'title' => 'Create Keyword Type',
            'active_menu' => 'keyword_type',
        ];

        $this->render('configuration/keyword_types/create', $data);
        return;
    }

    public function edit($id)
    {
        $keyword_type = $this->Keyword_type_model->get_by_id($id);
        if (empty($keyword_type)) {
            $this->session->set_flashdata('errors', '<p>Keyword type not found.</p>');
            redirect('config/keyword-type');
            return;
        }

        if ($this->input->method() === 'post') {
            $old_name = $keyword_type->name;
            $new_name = $this->input->post('name');
            $is_unique = ($new_name === $old_name) ? '' : '|is_unique[keyword_types.name]';
            
            $this->form_validation->set_rules('name', 'Name', 'required' . $is_unique);
            if ($this->form_validation->run() === TRUE) {
                $update = $this->Keyword_type_model->update($id, [
                    'name' => $new_name,
                ]);

                if ($update <= 0) {
                    $this->session->set_flashdata('errors', 'Failed to update keyword type or name unchanged.');
                    redirect('config/keyword-type');
                    return;
                }

                $this->session->set_flashdata('success', 'Keyword type updated successfully.');
                redirect('config/keyword-type');
                return;
            }
        }

        $data = [
            'title' => 'Edit Keyword Type',
            'active_menu' => 'keyword_type',
            'keyword_type' => $keyword_type,
        ];

        $this->render('configuration/keyword_types/edit', $data);
        return;
    }

    public function delete($id)
    {
        if ($this->input->method() !== 'post') {
            redirect('config/keyword-type');
        }

        $keyword_type = $this->Keyword_type_model->get_by_id($id);
        if (empty($keyword_type)) {
            $this->session->set_flashdata('errors', '<p>Keyword type not found.</p>');
            redirect('config/keyword-type');
            return;
        }

        $deleted = $this->Keyword_type_model->delete($id);

        if ($deleted) {
            $this->session->set_flashdata('success', 'Keyword type deleted successfully.');
        } else {
            $this->session->set_flashdata('errors', '<p>Failed to delete keyword type. Please try again.</p>');
        }

        redirect('config/keyword-type');
    }
}
