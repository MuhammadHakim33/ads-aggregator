<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Client extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->require_role('manajemen');
        $this->load->model('Client_model');
        $this->load->model('Account_model');
    }

    public function index()
    {
        $filters = [
            'q' => $this->input->get('q'),
            'status' => $this->input->get('status')
        ];

        $data = [
            'title' => 'Client',
            'active_menu' => 'client',
            'filters' => $filters,
            'clients' => $this->Client_model->get_all($filters)
        ];

        $this->render('client/index', $data);
    }

    public function create()
    {
        if ($this->input->method() === 'post') {
            $rules = [
                [
                    'field' => 'company_name',
                    'label' => 'Company Name',
                    'rules' => 'trim|required|max_length[255]'
                ],
                [
                    'field' => 'pic_name',
                    'label' => 'PIC Name',
                    'rules' => 'trim|required|max_length[255]'
                ],
                [
                    'field' => 'ae_id',
                    'label' => 'Account Executive',
                    'rules' => 'trim|callback_ae_check'
                ]
            ];

            $create_account = $this->input->post('create_account') === '1';
            if ($create_account) {
                $rules[] = [
                    'field' => 'username',
                    'label' => 'Username/Name',
                    'rules' => 'trim|required|min_length[3]|max_length[50]'
                ];
                $rules[] = [
                    'field' => 'email',
                    'label' => 'Email',
                    'rules' => 'trim|required|valid_email|is_unique[accounts.email]'
                ];
                $rules[] = [
                    'field' => 'password',
                    'label' => 'Password',
                    'rules' => 'trim|required|min_length[6]'
                ];
            }

            $this->form_validation->set_rules($rules);

            if ($this->form_validation->run() === TRUE) {
                // start database transaction
                $this->db->trans_start();

                $client_data = [
                    'company_name' => $this->input->post('company_name'),
                    'pic_name' => $this->input->post('pic_name'),
                    'ae_id' => $this->input->post('ae_id') ?: null,
                    'is_active' => TRUE
                ];

                $client_id = $this->Client_model->insert($client_data);

                if ($client_id && $create_account) {
                    $this->db->where('name', 'client');
                    $role = $this->db->get('roles')->row();
                    $role_id = $role ? $role->id : 4;

                    $account_id = $this->Account_model->insert([
                        'name' => $this->input->post('username'),
                        'email' => strtolower($this->input->post('email')),
                        'password' => $this->input->post('password'),
                        'role_id' => $role_id,
                        'is_active' => TRUE
                    ]);

                    if ($account_id) {
                        $this->Client_model->update($client_id, ['account_id' => $account_id]);
                    }
                }

                $this->db->trans_complete();

                if ($this->db->trans_status() === TRUE) {
                    $this->session->set_flashdata('success', 'Client created successfully.');
                    redirect('client');
                    return;
                } else {
                    $this->session->set_flashdata('errors', '<p>Failed to create client. Please try again.</p>');
                }
            }
        }

        $data = [
            'title' => 'Create Client',
            'active_menu' => 'client',
            'ae_list' => $this->Account_model->get_all_ae()
        ];

        $this->render('client/create', $data);
    }

    public function edit($id)
    {
        // Load client data
        $client = $this->Client_model->get_by_id($id);
        if (!$client) {
            $this->session->set_flashdata('errors', '<p>Client not found.</p>');
            redirect('client');
            return;
        }

        $account = null;
        if (!empty($client->account_id)) {
            $account = $this->Account_model->get_by_id($client->account_id);
        }

        if ($this->input->method() === 'post') {
            // Check if user requested to unlink the account
            if ($this->input->post('action') === 'unlink') {
                if ($account) {
                    $this->db->trans_start();
                    $this->Account_model->delete($account->id);
                    $this->Client_model->update($id, ['account_id' => NULL]);
                    $this->db->trans_complete();

                    if ($this->db->trans_status() === TRUE) {
                        $this->session->set_flashdata('success', 'Client account unlinked and deleted successfully.');
                    } else {
                        $this->session->set_flashdata('errors', '<p>Failed to unlink account. Please try again.</p>');
                    }
                }
                redirect('client/edit/' . $id);
                return;
            }

            $rules = [
                [
                    'field' => 'company_name',
                    'label' => 'Company Name',
                    'rules' => 'trim|required|max_length[255]'
                ],
                [
                    'field' => 'pic_name',
                    'label' => 'PIC Name',
                    'rules' => 'trim|required|max_length[255]'
                ],
                [
                    'field' => 'ae_id',
                    'label' => 'Account Executive',
                    'rules' => 'trim|callback_ae_check'
                ],
                [
                    'field' => 'is_active',
                    'label' => 'Status',
                    'rules' => 'in_list[0,1]'
                ]
            ];

            $has_account = $this->input->post('has_account') === '1';
            $create_account = $this->input->post('create_account') === '1';

            if ($has_account && $account) {
                $rules[] = [
                    'field' => 'username',
                    'label' => 'Username/Name',
                    'rules' => 'trim|required|min_length[3]|max_length[50]'
                ];
                $rules[] = [
                    'field' => 'email',
                    'label' => 'Email',
                    'rules' => 'trim|required|valid_email|callback_client_email_check[' . $account->id . ']'
                ];
                $rules[] = [
                    'field' => 'password',
                    'label' => 'Password',
                    'rules' => 'trim|min_length[6]'
                ];
                $rules[] = [
                    'field' => 'account_active',
                    'label' => 'Account Status',
                    'rules' => 'required|in_list[0,1]'
                ];
            } elseif ($create_account) {
                $rules[] = [
                    'field' => 'username',
                    'label' => 'Username/Name',
                    'rules' => 'trim|required|min_length[3]|max_length[50]'
                ];
                $rules[] = [
                    'field' => 'email',
                    'label' => 'Email',
                    'rules' => 'trim|required|valid_email|is_unique[accounts.email]'
                ];
                $rules[] = [
                    'field' => 'password',
                    'label' => 'Password',
                    'rules' => 'trim|required|min_length[6]'
                ];
            }

            $this->form_validation->set_rules($rules);

            if ($this->form_validation->run() === TRUE) {
                $this->db->trans_start();

                $client_data = [
                    'company_name' => $this->input->post('company_name'),
                    'pic_name' => $this->input->post('pic_name'),
                    'ae_id' => $this->input->post('ae_id') ?: null,
                    'is_active' => $this->input->post('is_active'),
                ];

                if ($has_account && $account) {
                    $acc_data = [
                        'name' => $this->input->post('username'),
                        'email' => strtolower($this->input->post('email')),
                        'is_active' => $this->input->post('account_active')
                    ];

                    $password = $this->input->post('password');
                    if ($password !== null && $password !== '') {
                        $acc_data['password'] = $password;
                    }

                    $this->Account_model->update($account->id, $acc_data);
                } elseif ($create_account) {
                    $this->db->where('name', 'client');
                    $role = $this->db->get('roles')->row();
                    $role_id = $role ? $role->id : 4;

                    $account_id = $this->Account_model->insert([
                        'name' => $this->input->post('username'),
                        'email' => strtolower($this->input->post('email')),
                        'password' => $this->input->post('password'),
                        'role_id' => $role_id,
                        'is_active' => TRUE
                    ]);

                    if ($account_id) {
                        $client_data['account_id'] = $account_id;
                    }
                }

                $this->Client_model->update($id, $client_data);

                $this->db->trans_complete();

                if ($this->db->trans_status() === TRUE) {
                    $this->session->set_flashdata('success', 'Client updated successfully.');
                    redirect('client');
                    return;
                } else {
                    $this->session->set_flashdata('errors', '<p>Failed to update client. Please try again.</p>');
                }
            }
        }

        $data = [
            'title' => 'Edit Client',
            'client' => $client,
            'account' => $account,
            'active_menu' => 'client',
            'ae_list' => $this->Account_model->get_all_ae()
        ];

        $this->render('client/edit', $data);
    }

    public function delete($id)
    {
        if ($this->input->method() !== 'post') {
            redirect('client');
        }

        // load client data using callback method
        $client = $this->Client_model->get_by_id($id);
        if (!$client) {
            $this->session->set_flashdata('errors', '<p>Client not found.</p>');
            redirect('client');
            return;
        }

        $this->db->trans_start();

        if (!empty($client->account_id)) {
            $this->Account_model->delete($client->account_id);
        }

        $this->Client_model->delete($id);

        $this->db->trans_complete();

        if ($this->db->trans_status() === TRUE) {
            $this->session->set_flashdata('success', 'Client deleted successfully.');
        } else {
            $this->session->set_flashdata('errors', '<p>Failed to delete client. Please try again.</p>');
        }

        redirect('client');
    }

    public function ae_check($ae_id)
    {
        if (empty($ae_id)) {
            return TRUE;
        }

        if ($this->Account_model->is_ae_exist_by_id($ae_id) == 0) {
            $this->form_validation->set_message([
                'ae_check' => 'The selected Account Executive does not exist or is inactive'
            ]);
            return FALSE;
        }

        return TRUE;
    }


    public function client_email_check($email, $account_id)
    {
        if ($this->Account_model->is_email_used($email, $account_id) > 0) {
            $this->form_validation->set_message([
                'client_email_check' => 'Email has already been used'
            ]);
            return FALSE;
        }
        return TRUE;
    }
}
