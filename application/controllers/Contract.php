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

        // Prepare view data
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
            $client_id = $client ? $client->id : -1;
            if ($contract->client_id != $client_id) {
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

    public function get_product_json($id)
    {
        $this->require_role('manajemen', 'client');
        $product = $this->Product_model->get_by_id($id);
        if ($product) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($product));
        } else {
            $this->output
                ->set_status_header(404)
                ->set_output(json_encode(['error' => 'Product not found']));
        }
    }

    public function get_items_json($contract_id)
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
            $client_id = $client ? $client->id : -1;
            if ($contract->client_id != $client_id) {
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

    public function migrate()
    {
        if (ENVIRONMENT !== 'development' && $this->current_account['role'] !== 'superadmin') {
            show_error('Unauthorized', 403);
            return;
        }

        $this->db->trans_start();

        if (!$this->db->field_exists('status', 'contracts')) {
            $this->db->query("ALTER TABLE contracts 
                ADD COLUMN status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'approved',
                ADD COLUMN rejection_reason TEXT NULL,
                ADD COLUMN approved_by INT NULL,
                ADD COLUMN approved_at TIMESTAMP NULL,
                ADD CONSTRAINT fk_contracts_approved_by FOREIGN KEY (approved_by) REFERENCES accounts(id)");
            echo "Added status and approval columns to contracts.<br>";
        }

        if (!$this->db->table_exists('products')) {
            $this->db->query("CREATE TABLE products (
              id INT PRIMARY KEY AUTO_INCREMENT,
              category ENUM('content_marketing', 'banner_ads', 'social_media') NOT NULL,
              name VARCHAR(255) NOT NULL,
              platform_type ENUM('desktop', 'mobile', 'social') NOT NULL,
              price_model ENUM('cpm', 'per_day', 'per_week', 'fixed') NOT NULL,
              price DECIMAL(15,2) NOT NULL,
              is_active BOOLEAN DEFAULT TRUE,
              created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            echo "Created products table.<br>";

            $this->db->query("INSERT INTO products (category, name, platform_type, price_model, price, is_active) VALUES
                ('content_marketing', 'Content Partnership - Khas', 'desktop', 'fixed', 15000000.00, 1),
                ('content_marketing', 'Content Partnership - Khas Korporasi & Kementerian', 'desktop', 'fixed', 25000000.00, 1),
                ('banner_ads', 'Masthead Desktop', 'desktop', 'cpm', 50000.00, 1),
                ('banner_ads', 'Leaderboard Desktop', 'desktop', 'cpm', 35000.00, 1),
                ('banner_ads', 'Billboard Desktop', 'desktop', 'cpm', 40000.00, 1),
                ('banner_ads', 'Masthead Mobile', 'mobile', 'cpm', 45000.00, 1),
                ('banner_ads', 'Mid-article Mobile', 'mobile', 'cpm', 30000.00, 1),
                ('social_media', 'Instagram Feed Post', 'social', 'fixed', 5000000.00, 1),
                ('social_media', 'YouTube Video Integration', 'social', 'fixed', 12500000.00, 1)");
            echo "Seeded products table.<br>";
        }

        if (!$this->db->table_exists('contract_items')) {
            $this->db->query("CREATE TABLE contract_items (
              id INT PRIMARY KEY AUTO_INCREMENT,
              contract_id INT NOT NULL,
              product_id INT NOT NULL,
              quantity INT NOT NULL DEFAULT 1,
              price DECIMAL(15,2) NOT NULL COMMENT 'Snapshot harga saat dibuat',
              subtotal DECIMAL(15,2) NOT NULL,
              
              FOREIGN KEY (contract_id) REFERENCES contracts(id) ON DELETE CASCADE,
              FOREIGN KEY (product_id) REFERENCES products(id)
            )");
            echo "Created contract_items table.<br>";
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo "Migration failed!";
        } else {
            echo "Migration completed successfully!";
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
