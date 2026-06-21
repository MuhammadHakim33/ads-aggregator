<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Contract extends MY_Controller
{
    private $editing_id = null;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Contract_model');
        $this->load->model('Client_model');
        $this->load->model('Campaign_model');
        $this->load->helper('download');
    }

    public function index()
    {
        $filters = [
            'q' => $this->input->get('q'),
            'client_id' => $this->input->get('client_id')
        ];

        $contracts = $this->Contract_model->get_all($filters);

        // build campaigns map keyed by contract_id
        $campaigns_by_contract = [];
        foreach ($contracts as $contract) {
            $campaigns_by_contract[$contract->id] = $this->Campaign_model->get_by_contract_id($contract->id);
        }

        $data = [
            'title' => 'Contracts',
            'active_menu' => 'contract',
            'filters' => $filters,
            'contracts' => $contracts,
            'clients' => $this->Client_model->get_all(),
            'campaigns_by_contract' => $campaigns_by_contract
        ];

        $this->render('contract/index', $data);
    }

    public function create()
    {
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules([
                [
                    'field' => 'client_id',
                    'label' => 'Client',
                    'rules' => 'trim|required|integer|callback_client_check'
                ],
                [
                    'field' => 'value',
                    'label' => 'Value',
                    'rules' => 'trim|required|numeric|greater_than[0]'
                ],
                [
                    'field' => 'start_date',
                    'label' => 'Start Date',
                    'rules' => 'trim|required|exact_length[10]|callback_valid_date'
                ],
                [
                    'field' => 'end_date',
                    'label' => 'End Date',
                    'rules' => 'trim|required|exact_length[10]|callback_valid_date|callback_date_range_check'
                ]
            ]);

            if ($this->form_validation->run() === TRUE) {
                $client = $this->Client_model->get_by_id($this->input->post('client_id'));
                if (!$client) {
                    $this->session->set_flashdata('errors', 'Selected Client not found.');
                    redirect('contract/create');
                    return;
                }

                // handle file upload if present
                $document_path = null;
                if (!empty($_FILES['document']['name'])) {
                    $config['upload_path'] = './uploads/contracts/';
                    $config['allowed_types'] = 'pdf|doc|docx';
                    $config['max_size'] = 5120; // 5MB
                    $config['encrypt_name'] = TRUE;

                    $this->load->library('upload', $config);

                    if ($this->upload->do_upload('document')) {
                        $upload_data = $this->upload->data();
                        $document_path = 'uploads/contracts/' . $upload_data['file_name'];
                    } else {
                        $this->session->set_flashdata('errors', $this->upload->display_errors());
                        redirect('contract/create');
                        return;
                    }
                }

                // auto generate contract number and validate until unique
                do {
                    $contract_number = $this->generate_contract_number($client->company_name);
                } while (!$this->Contract_model->is_contract_number_unique($contract_number));

                $insert_id = $this->Contract_model->insert([
                    'client_id' => $this->input->post('client_id'),
                    'contract_number' => $contract_number,
                    'value' => $this->input->post('value'),
                    'start_date' => $this->input->post('start_date'),
                    'end_date' => $this->input->post('end_date'),
                    'document_path' => $document_path
                ]);

                if ($insert_id) {
                    $this->session->set_flashdata('success', 'Contract created successfully.');
                    redirect('contract');
                    return;
                } else {
                    $this->session->set_flashdata('errors', 'Failed to create contract. Please try again.');
                }
            }
        }

        $data = [
            'title' => 'Create Contract',
            'active_menu' => 'contract',
            'clients' => $this->Client_model->get_all()
        ];

        $this->render('contract/create', $data);
    }

    public function edit($id)
    {
        $contract = $this->Contract_model->get_by_id($id);
        if (!$contract) {
            $this->session->set_flashdata('errors', 'Contract not found.');
            redirect('contract');
            return;
        }

        $this->editing_id = $id;

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules([
                [
                    'field' => 'client_id',
                    'label' => 'Client',
                    'rules' => 'trim|required|integer|callback_client_check'
                ],
                [
                    'field' => 'value',
                    'label' => 'Value',
                    'rules' => 'trim|required|numeric|greater_than[0]'
                ],
                [
                    'field' => 'start_date',
                    'label' => 'Start Date',
                    'rules' => 'trim|required|exact_length[10]|callback_valid_date'
                ],
                [
                    'field' => 'end_date',
                    'label' => 'End Date',
                    'rules' => 'trim|required|exact_length[10]|callback_valid_date|callback_date_range_check'
                ],
                [
                    'field' => 'termination_reason',
                    'label' => 'Termination Reason',
                    'rules' => 'trim'
                ]
            ]);

            if ($this->form_validation->run() === TRUE) {
                $update_data = [
                    'client_id' => $this->input->post('client_id'),
                    'value' => $this->input->post('value'),
                    'start_date' => $this->input->post('start_date'),
                    'end_date' => $this->input->post('end_date'),
                ];

                // check termination toggle
                $is_terminated = $this->input->post('is_terminated');
                if ($is_terminated) {
                    $update_data['terminated_at'] = $this->input->post('terminated_at') ?: date('Y-m-d H:i:s');
                    $update_data['termination_reason'] = $this->input->post('termination_reason');

                    // Set all campaigns under this contract to inactive
                    $this->Campaign_model->deactivate_by_contract($id);
                } else {
                    $update_data['terminated_at'] = null;
                    $update_data['termination_reason'] = null;
                }

                // handle file upload if present
                if (!empty($_FILES['document']['name'])) {
                    $config['upload_path'] = './uploads/contracts/';
                    $config['allowed_types'] = 'pdf|doc|docx';
                    $config['max_size'] = 5120; // 5MB
                    $config['encrypt_name'] = TRUE;

                    $this->load->library('upload', $config);

                    if ($this->upload->do_upload('document')) {
                        $upload_data = $this->upload->data();
                        $update_data['document_path'] = 'uploads/contracts/' . $upload_data['file_name'];

                        // optionally delete old file
                        if ($contract->document_path && file_exists('./' . $contract->document_path)) {
                            unlink('./' . $contract->document_path);
                        }
                    } else {
                        $this->session->set_flashdata('errors', $this->upload->display_errors());
                        redirect('contract/edit/' . $id);
                        return;
                    }
                }

                $updated = $this->Contract_model->update($id, $update_data);

                $this->session->set_flashdata('success', 'Contract updated successfully.');
                redirect('contract');
                return;
            }
        }

        $data = [
            'title' => 'Edit Contract',
            'active_menu' => 'contract',
            'contract' => $contract,
            'clients' => $this->Client_model->get_all()
        ];

        $this->render('contract/edit', $data);
    }

    public function delete($id)
    {
        if ($this->input->method() !== 'post') {
            redirect('contract');
            return;
        }

        $contract = $this->Contract_model->get_by_id($id);
        if (!$contract) {
            $this->session->set_flashdata('errors', 'Contract not found.');
            redirect('contract');
            return;
        }

        // check if contract has campaigns (optional check for security)
        if ($this->Contract_model->has_campaigns($id)) {
            $this->session->set_flashdata('errors', 'Cannot delete contract. It has active campaigns associated with it.');
            redirect('contract');
            return;
        }

        $deleted = $this->Contract_model->delete($id);
        if ($deleted) {
            $this->session->set_flashdata('success', 'Contract deleted successfully.');
        } else {
            $this->session->set_flashdata('errors', 'Failed to delete contract. Please try again.');
        }

        redirect('contract');
    }

    public function download($id)
    {
        $contract = $this->Contract_model->get_by_id($id);
        if (!$contract || !$contract->document_path) {
            show_404();
            return;
        }

        $file_path = './' . $contract->document_path;
        if (file_exists($file_path)) {
            force_download($file_path, NULL);
        } else {
            $this->session->set_flashdata('errors', 'Contract document file not found on server.');
            redirect('contract');
        }
    }

    public function client_check($client_id)
    {
        $client = $this->Client_model->get_by_id($client_id);
        if (!$client) {
            $this->form_validation->set_message([
                'client_check' => 'The selected Client does not exist or is inactive.'
            ]);
            return FALSE;
        }
        return TRUE;
    }

    public function contract_number_check($contract_number)
    {
        $is_unique = $this->Contract_model->is_contract_number_unique($contract_number, $this->editing_id);

        if (!$is_unique) {
            $this->form_validation->set_message([
                'contract_number_check' => 'The Contract Number is already in use.'
            ]);
            return FALSE;
        }
        return TRUE;
    }

    public function valid_date($date)
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        if ($d && $d->format('Y-m-d') === $date) {
            return TRUE;
        }
        $this->form_validation->set_message([
            'valid_date' => 'The {field} field must be in YYYY-MM-DD format.'
        ]);
        return FALSE;
    }

    public function date_range_check($end_date)
    {
        $start_date = $this->input->post('start_date');
        if (strtotime($end_date) < strtotime($start_date)) {
            $this->form_validation->set_message([
                'date_range_check' => 'The End Date must be equal to or after the Start Date.'
            ]);
            return FALSE;
        }
        return TRUE;
    }

    public function generate_contract_number($client_name)
    {
        $prefix = "KTN";
        $yearMonth = date('Y/m');
        $microtime = microtime(true);

        $timeHex = strtoupper(dechex((int) ($microtime * 1000000)));
        $last_three = substr($timeHex, -4);

        $client = $this->generate_abbreviation($client_name);

        return $prefix . "/" . $yearMonth . "/" . $client . "/" . $last_three;
    }

    public function generate_abbreviation(string $text): string
    {
        if (preg_match_all('/\b\w/u', $text, $matches)) {
            return strtoupper(implode('', $matches[0]));
        }

        return '';
    }
}
