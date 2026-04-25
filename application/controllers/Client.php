<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Client extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Client_model');
        $this->load->model('Account_model');
        $this->load->model('Client_identifier_model');
    }

    public function index()
    {
        $clients = $this->Client_model->get_all();

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'data' => $clients
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

        $this->form_validation->set_rules([
            [
                'field' => 'company_name',
                'label' => 'Company Name',
                'rules' => 'trim|required|max_length[255]'
            ],
            [
                'field' => 'pic_name',
                'label' => 'PIC Name',
                'rules' => 'trim|max_length[255]'
            ],
            [
                'field' => 'ae_id',
                'label' => 'AE ID',
                'rules' => 'required|integer|callback_ae_id_check'
            ]
        ]);

        if ($this->form_validation->run() === FALSE) {
            return $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => validation_errors()]));
        }

        $insert_id = $this->Client_model->insert([
            'company_name' => $this->input->post('company_name'),
            'pic_name' => $this->input->post('pic_name'),
            'ae_id' => $this->input->post('ae_id'),
            'is_active' => TRUE
        ]);

        if ($insert_id) {
            $this->output
                ->set_status_header(201)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'success',
                    'message' => 'Client created successfully',
                    'data' => ['id' => $insert_id]
                ]));
        } else {
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Failed to create client']));
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
                ->set_output(json_encode(['status' => 'error', 'message' => 'Client ID is required']));
        }

        if ($this->Client_model->is_exist_by_id($id) == 0) {
            return $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Client not found']));
        }

        $this->form_validation->set_rules([
            [
                'field' => 'company_name',
                'label' => 'Company Name',
                'rules' => 'trim|max_length[255]'
            ],
            [
                'field' => 'pic_name',
                'label' => 'PIC Name',
                'rules' => 'trim|max_length[255]'
            ],
            [
                'field' => 'ae_id',
                'label' => 'AE ID',
                'rules' => 'trim|integer|callback_ae_id_check'
            ],
            [
                'field' => 'is_active',
                'label' => 'Is Active',
                'rules' => 'in_list[0,1]'
            ]
        ]);

        if ($this->form_validation->run() === FALSE) {
            return $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => validation_errors()]));
        }

        $data = [];

        $company_name = $this->input->post('company_name');
        if ($company_name !== null) $data['company_name'] = $company_name;

        $pic_name = $this->input->post('pic_name');
        if ($pic_name !== null) $data['pic_name'] = $pic_name;

        $ae_id = $this->input->post('ae_id');
        if ($ae_id !== null) $data['ae_id'] = $ae_id;

        $is_active = $this->input->post('is_active');
        if ($is_active !== null) $data['is_active'] = $is_active;

        if (empty($data)) {
            return $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'No data to update']));
        }

        $updated = $this->Client_model->update($id, $data);

        if ($updated) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'success', 'message' => 'Client updated successfully']));
        } else {
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Failed to update client']));
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
                ->set_output(json_encode(['status' => 'error', 'message' => 'Client ID is required']));
        }

        if ($this->Client_model->is_exist_by_id($id) == 0) {
            return $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Client not found']));
        }

        $deleted = $this->Client_model->delete($id);

        if ($deleted) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'success', 'message' => 'Client deleted successfully']));
        } else {
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Failed to delete client']));
        }
    }

    public function identifiers($client_id)
    {
        if (!$client_id) {
            return $this->output->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Client ID is required']));
        }

        if ($this->Client_model->is_exist_by_id($client_id) == 0) {
            return $this->output->set_status_header(404)
            ->set_content_type('application/json')
            ->set_output(json_encode(['status' => 'error', 'message' => 'Client not found']));
        }

        $identifiers = $this->Client_identifier_model->get_by_client_id($client_id);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'data' => $identifiers
            ]));
    }

    public function create_identifier($client_id)
    {
        if (!$client_id || $this->Client_model->is_exist_by_id($client_id) == 0) {
            return $this->output->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Client not found']));
        }

        $this->form_validation->set_rules([
            [
                'field' => 'platform',
                'label' => 'Platform',
                'rules' => 'required|in_list[meta,gam,ga4,yt]'
            ],
            [
                'field' => 'identifier',
                'label' => 'Identifier',
                'rules' => 'required|max_length[255]'
            ]
        ]);

        if ($this->form_validation->run() === FALSE) {
            return $this->output->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => validation_errors()]));
        }

        $platform = $this->input->post('platform');
        $identifier = $this->input->post('identifier');

        if ($this->Client_identifier_model->check_duplicate($client_id, $platform, $identifier)) {
            return $this->output->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Identifier already exists for this client and platform']));
        }

        $insert_id = $this->Client_identifier_model->insert([
            'client_id' => $client_id,
            'platform' => $platform,
            'identifier' => $identifier,
            'is_active' => 1
        ]);

        if ($insert_id) {
            $this->output->set_status_header(201)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'success', 'message' => 'Identifier added successfully', 'data' => ['id' => $insert_id]]));
        } else {
            $this->output->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Failed to add identifier']));
        }
    }

    public function update_identifier($id)
    {
        if (!$id || $this->Client_identifier_model->is_exist_by_id($id) == 0) {
            return $this->output->set_status_header(404)->set_content_type('application/json')->set_output(json_encode(['status' => 'error', 'message' => 'Identifier not found']));
        }

        $data = [];

        $platform = $this->input->input_stream('platform');
        $identifier = $this->input->input_stream('identifier');
        $is_active = $this->input->input_stream('is_active');

        if ($platform !== null) $data['platform'] = $platform;
        if ($identifier !== null) $data['identifier'] = $identifier;
        if ($is_active !== null) $data['is_active'] = $is_active;

        $this->form_validation->set_data($data);

        $this->form_validation->set_rules([
            [
                'field' => 'platform',
                'label' => 'Platform',
                'rules' => 'in_list[meta,gam,ga4,yt]'
            ],
            [
                'field' => 'identifier',
                'label' => 'Identifier',
                'rules' => 'max_length[255]'
            ],
            [
                'field' => 'is_active',
                'label' => 'Active Status',
                'rules' => 'in_list[0,1]'
            ]
        ]);

        if ($this->form_validation->run() === FALSE) {
            return $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(['status' => 'error', 'message' => validation_errors()]));
        }

        if (empty($data)) {
            return $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(['status' => 'error', 'message' => 'No data to update']));
        }

        $updated = $this->Client_identifier_model->update($id, $data);

        if ($updated) {
            $this->output->set_content_type('application/json')->set_output(json_encode(['status' => 'success', 'message' => 'Identifier updated successfully']));
        } else {
            $this->output->set_status_header(500)->set_content_type('application/json')->set_output(json_encode(['status' => 'error', 'message' => 'Failed to update identifier']));
        }
    }

    public function delete_identifier($id)
    {
        if (!$id) {
            return $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Identifier ID is required']));
        }

        if ($this->Client_identifier_model->is_exist_by_id($id) == 0) {
            return $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Identifier not found']));
        }

        $deleted = $this->Client_identifier_model->delete($id);

        if ($deleted) {
            $this->output->set_content_type('application/json')->set_output(json_encode(['status' => 'success', 'message' => 'Identifier deleted successfully']));
        } else {
            $this->output->set_status_header(500)->set_content_type('application/json')->set_output(json_encode(['status' => 'error', 'message' => 'Failed to delete identifier']));
        }
    }


    public function ae_id_check($ae_id)
    {
        if ($ae_id === null || $ae_id === '') {
            return TRUE;
        }

        if ($this->Account_model->is_ae_exist_by_id($ae_id) == 0) {
            $this->form_validation->set_message(
                'ae_id_check',
                'AE ID does not exist'
            );
            return FALSE;
        }

        return TRUE;
    }
}
