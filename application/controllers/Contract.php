<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Contract extends MY_Controller
{
    private $editing_id = null;

    public function __construct()
    {
        parent::__construct();
        $this->require_role('manajemen', 'client');
        $this->load->model('Contract_model');
        $this->load->model('Client_model');
        $this->load->model('Campaign_model');
        $this->load->model('Product_model');
        $this->load->helper('download');
    }

    public function index()
    {
        $filters = [
            'q' => $this->input->get('q'),
            'client_id' => $this->input->get('client_id')
        ];

        if ($this->current_account['role'] === 'client') {
            $client = $this->Client_model->get_by_account_id($this->current_account['id']);
            $filters['client_id'] = $client ? $client->id : -1;
        } elseif ($this->current_account['role'] === 'ae') {
            $filters['ae_id'] = $this->current_account['id'];
        }

        $contracts = $this->Contract_model->get_all($filters);

        // build campaigns map keyed by contract_id
        $campaigns_by_contract = [];
        foreach ($contracts as $contract) {
            $campaigns_by_contract[$contract->id] = $this->Campaign_model->get_by_contract_id($contract->id);
        }

        // clients dropdown filter options
        $client_options_filters = [];
        if ($this->current_account['role'] !== 'client') {
            $client_filters = [];
            if ($this->current_account['role'] === 'ae') {
                $client_filters['ae_id'] = $this->current_account['id'];
            }
            $client_options_filters = $this->Client_model->get_all($client_filters);
        }

        $data = [
            'title' => 'Contracts',
            'active_menu' => 'contract',
            'filters' => $filters,
            'contracts' => $contracts,
            'clients' => $client_options_filters,
            'campaigns_by_contract' => $campaigns_by_contract
        ];

        $this->render('contract/index', $data);
    }

    public function create()
    {
        $this->require_role('manajemen', 'client');

        $role = $this->current_account['role'];
        $client_id = null;
        $client = null;
        if ($role === 'client') {
            $client = $this->Client_model->get_by_account_id($this->current_account['id']);
            if (!$client) {
                $this->session->set_flashdata('errors', 'Client profile not found.');
                redirect('contract');
                return;
            }
            $client_id = $client->id;
        }

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules([
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

            if ($role !== 'client') {
                $this->form_validation->set_rules([
                    [
                        'field' => 'client_id',
                        'label' => 'Client',
                        'rules' => 'trim|required|integer|callback_client_check'
                    ]
                ]);
            }

            if ($this->form_validation->run() === TRUE) {
                if ($role !== 'client') {
                    $client_id = $this->input->post('client_id');
                    $client = $this->Client_model->get_by_id($client_id);
                }

                if (!$client) {
                    $this->session->set_flashdata('errors', 'Selected Client not found.');
                    redirect('contract/create');
                    return;
                }

                // Validate products
                $product_ids = $this->input->post('product_id');
                $quantities = $this->input->post('quantity');

                if (empty($product_ids) || !is_array($product_ids) || count($product_ids) === 0) {
                    $this->session->set_flashdata('errors', 'Please add at least one product.');
                    redirect('contract/create');
                    return;
                }

                $items_to_insert = [];
                $total_value = 0;

                for ($i = 0; $i < count($product_ids); $i++) {
                    $prod_id = intval($product_ids[$i]);
                    $qty = intval($quantities[$i]);

                    if ($qty <= 0) {
                        $this->session->set_flashdata('errors', 'Quantity must be greater than zero.');
                        redirect('contract/create');
                        return;
                    }

                    $product = $this->Product_model->get_by_id($prod_id);
                    if (!$product) {
                        $this->session->set_flashdata('errors', 'Selected product is invalid or inactive.');
                        redirect('contract/create');
                        return;
                    }

                    // Calculate subtotal
                    if ($product->price_model === 'cpm') {
                        $subtotal = ($qty / 1000) * $product->price;
                    } else {
                        $subtotal = $qty * $product->price;
                    }

                    $total_value += $subtotal;
                    $items_to_insert[] = [
                        'product_id' => $prod_id,
                        'quantity' => $qty,
                        'price' => $product->price,
                        'subtotal' => $subtotal
                    ];
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

                $status = ($role === 'client') ? 'pending' : 'approved';

                $contract_data = [
                    'client_id' => $client_id,
                    'contract_number' => $contract_number,
                    'value' => $total_value,
                    'start_date' => $this->input->post('start_date'),
                    'end_date' => $this->input->post('end_date'),
                    'document_path' => $document_path,
                    'status' => $status
                ];

                if ($status === 'approved') {
                    $contract_data['approved_by'] = $this->current_account['id'];
                    $contract_data['approved_at'] = date('Y-m-d H:i:s');
                }

                $this->db->trans_start();

                $insert_id = $this->Contract_model->insert($contract_data);

                if ($insert_id) {
                    foreach ($items_to_insert as $item) {
                        $item['contract_id'] = $insert_id;
                        $this->Contract_model->insert_item($item);
                    }
                }

                $this->db->trans_complete();

                if ($this->db->trans_status() === TRUE && $insert_id) {
                    $msg = ($status === 'pending') ? 'Contract submitted for approval.' : 'Contract created successfully.';
                    $this->session->set_flashdata('success', $msg);
                    redirect('contract');
                    return;
                } else {
                    $this->session->set_flashdata('errors', 'Failed to create contract. Please try again.');
                }
            }
        }

        $clients_list = ($role === 'client') ? [] : $this->Client_model->get_all();
        $products = $this->Product_model->get_all_active();

        $data = [
            'title' => 'Create Contract',
            'active_menu' => 'contract',
            'clients' => $clients_list,
            'products' => $products,
            'client_company_name' => ($role === 'client') ? $client->company_name : ''
        ];

        $this->render('contract/create', $data);
    }

    public function edit($id)
    {
        $this->require_role('manajemen', 'client');
        $contract = $this->Contract_model->get_by_id($id);
        if (!$contract) {
            $this->session->set_flashdata('errors', 'Contract not found.');
            redirect('contract');
            return;
        }

        $role = $this->current_account['role'];
        if ($role === 'client') {
            $client = $this->Client_model->get_by_account_id($this->current_account['id']);
            $client_id = $client ? $client->id : -1;
            if ($contract->client_id != $client_id) {
                show_error('Unauthorized', 403);
                return;
            }
            if (!in_array($contract->status, ['pending', 'rejected'])) {
                show_error('You cannot edit an approved contract.', 403);
                return;
            }
        }

        $this->editing_id = $id;

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules([
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

            if ($role !== 'client') {
                $this->form_validation->set_rules([
                    [
                        'field' => 'client_id',
                        'label' => 'Client',
                        'rules' => 'trim|required|integer|callback_client_check'
                    ],
                    [
                        'field' => 'termination_reason',
                        'label' => 'Termination Reason',
                        'rules' => 'trim'
                    ]
                ]);
            }

            if ($this->form_validation->run() === TRUE) {
                // Validate products
                $product_ids = $this->input->post('product_id');
                $quantities = $this->input->post('quantity');

                if (empty($product_ids) || !is_array($product_ids) || count($product_ids) === 0) {
                    $this->session->set_flashdata('errors', 'Please add at least one product.');
                    redirect('contract/edit/' . $id);
                    return;
                }

                $items_to_insert = [];
                $total_value = 0;

                for ($i = 0; $i < count($product_ids); $i++) {
                    $prod_id = intval($product_ids[$i]);
                    $qty = intval($quantities[$i]);

                    if ($qty <= 0) {
                        $this->session->set_flashdata('errors', 'Quantity must be greater than zero.');
                        redirect('contract/edit/' . $id);
                        return;
                    }

                    $product = $this->Product_model->get_by_id($prod_id);
                    if (!$product) {
                        $this->session->set_flashdata('errors', 'Selected product is invalid or inactive.');
                        redirect('contract/edit/' . $id);
                        return;
                    }

                    if ($product->price_model === 'cpm') {
                        $subtotal = ($qty / 1000) * $product->price;
                    } else {
                        $subtotal = $qty * $product->price;
                    }

                    $total_value += $subtotal;
                    $items_to_insert[] = [
                        'contract_id' => $id,
                        'product_id' => $prod_id,
                        'quantity' => $qty,
                        'price' => $product->price,
                        'subtotal' => $subtotal
                    ];
                }

                $update_data = [
                    'value' => $total_value,
                    'start_date' => $this->input->post('start_date'),
                    'end_date' => $this->input->post('end_date'),
                ];

                if ($role === 'client') {
                    if ($contract->status === 'rejected') {
                        $update_data['status'] = 'pending';
                        $update_data['rejection_reason'] = null;
                        $update_data['approved_by'] = null;
                        $update_data['approved_at'] = null;
                    }
                } else {
                    $update_data['client_id'] = $this->input->post('client_id');

                    $is_terminated = $this->input->post('is_terminated');
                    if ($is_terminated) {
                        $update_data['terminated_at'] = $this->input->post('terminated_at') ?: date('Y-m-d H:i:s');
                        $update_data['termination_reason'] = $this->input->post('termination_reason');
                        $this->Campaign_model->deactivate_by_contract($id);
                    } else {
                        $update_data['terminated_at'] = null;
                        $update_data['termination_reason'] = null;
                    }
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

                        // delete old file
                        if ($contract->document_path && file_exists('./' . $contract->document_path)) {
                            unlink('./' . $contract->document_path);
                        }
                    } else {
                        $this->session->set_flashdata('errors', $this->upload->display_errors());
                        redirect('contract/edit/' . $id);
                        return;
                    }
                }

                $this->db->trans_start();

                $this->Contract_model->update($id, $update_data);
                $this->Contract_model->delete_items($id);
                foreach ($items_to_insert as $item) {
                    $this->Contract_model->insert_item($item);
                }

                $this->db->trans_complete();

                if ($this->db->trans_status() === TRUE) {
                    $msg = ($role === 'client' && $contract->status === 'rejected') ? 'Contract resubmitted for approval.' : 'Contract updated successfully.';
                    $this->session->set_flashdata('success', $msg);
                    redirect('contract');
                    return;
                } else {
                    $this->session->set_flashdata('errors', 'Failed to update contract. Please try again.');
                }
            }
        }

        $data = [
            'title' => 'Edit Contract',
            'active_menu' => 'contract',
            'contract' => $contract,
            'clients' => ($role === 'client') ? [] : $this->Client_model->get_all(),
            'products' => $this->Product_model->get_all_active(),
            'contract_items' => $this->Contract_model->get_items($id)
        ];

        $this->render('contract/edit', $data);
    }

    public function delete($id)
    {
        $this->require_role('manajemen');
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

        $role = $this->current_account['role'];
        if ($role === 'client') {
            $client = $this->Client_model->get_by_account_id($this->current_account['id']);
            if ($contract->client_id !== $client->id) {
                show_error('Unauthorized', 403);
                return;
            }
        }

        $file_path = './' . $contract->document_path;
        if (file_exists($file_path)) {
            force_download($file_path, NULL);
        } else {
            $this->session->set_flashdata('errors', 'Contract document file not found on server.');
            redirect('contract');
        }
    }

    public function approve($id)
    {
        $this->require_role('manajemen');
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

        $updated = $this->Contract_model->update($id, [
            'status' => 'approved',
            'approved_by' => $this->current_account['id'],
            'approved_at' => date('Y-m-d H:i:s'),
            'rejection_reason' => null
        ]);

        if ($updated) {
            $this->session->set_flashdata('success', 'Contract approved successfully.');
        } else {
            $this->session->set_flashdata('errors', 'Failed to approve contract.');
        }

        redirect('contract');
    }

    public function reject($id)
    {
        $this->require_role('manajemen');
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

        $reason = $this->input->post('rejection_reason');
        if (empty($reason)) {
            $this->session->set_flashdata('errors', 'Rejection reason is required.');
            redirect('contract');
            return;
        }

        $updated = $this->Contract_model->update($id, [
            'status' => 'rejected',
            'approved_by' => $this->current_account['id'],
            'approved_at' => date('Y-m-d H:i:s'),
            'rejection_reason' => $reason
        ]);

        if ($updated) {
            $this->session->set_flashdata('success', 'Contract rejected successfully.');
        } else {
            $this->session->set_flashdata('errors', 'Failed to reject contract.');
        }

        redirect('contract');
    }

    public function get_detail_json($contract_id)
    {
        $this->require_role('manajemen', 'client');
        $contract = $this->Contract_model->get_by_id($contract_id);
        if (!$contract) {
            $this->output->set_status_header(404)->set_output(json_encode(['error' => 'Contract not found']));
            return;
        }

        $role = $this->current_account['role'];
        if ($role === 'client') {
            $client = $this->Client_model->get_by_account_id($this->current_account['id']);
            if ($contract->client_id !== $client->id) {
                $this->output->set_status_header(403)->set_output(json_encode(['error' => 'Unauthorized']));
                return;
            }
        }

        $items = $this->Contract_model->get_items($contract_id);
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'contract' => $contract,
                'items' => $items
            ]));
    }

    // custom validation
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

    // helper
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
