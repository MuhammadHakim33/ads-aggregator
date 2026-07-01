<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pic extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->require_role('manajemen');
        $this->load->model('Client_model');
        $this->load->model('Client_pic_model');
        $this->load->model('Account_model');
    }

    public function index()
    {
        $client_id = $this->input->get('client_id');
        $client = $this->Client_model->get_by_id($client_id);

        if (!$client) {
            $this->session->set_flashdata('errors', '<p>Client not found.</p>');
            redirect('client');
            return;
        }

        $pics = $this->Client_pic_model->get_by_client_id($client_id);

        $data = [
            'title' => 'Client PICs',
            'active_menu' => 'client',
            'client' => $client,
            'pics' => $pics
        ];

        $this->render('pic/index', $data);
    }

    public function create()
    {
        $client_id = $this->input->get('client_id');
        $client = $this->Client_model->get_by_id($client_id);

        if (!$client) {
            $this->session->set_flashdata('errors', '<p>Client not found.</p>');
            redirect('client');
            return;
        }

        if ($this->input->method() === 'post') {
            $rules = [
                [
                    'field' => 'name',
                    'label' => 'PIC Name',
                    'rules' => 'trim|required|max_length[255]'
                ],
                [
                    'field' => 'position',
                    'label' => 'Position',
                    'rules' => 'trim|max_length[255]'
                ]
            ];

            $create_account = $this->input->post('create_account') === '1';
            if ($create_account) {
                $rules[] = [
                    'field' => 'username',
                    'label' => 'Username',
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

                $account_id = null;
                if ($create_account) {
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
                }

                $pic_data = [
                    'client_id' => $client_id,
                    'name' => $this->input->post('name'),
                    'position' => $this->input->post('position') ?: null,
                    'account_id' => $account_id,
                    'is_active' => TRUE
                ];

                $this->Client_pic_model->insert($pic_data);

                $this->db->trans_complete();

                if ($this->db->trans_status() === TRUE) {
                    $this->session->set_flashdata('success', 'PIC created successfully.');
                    redirect('pic?client_id=' . $client_id);
                    return;
                } else {
                    $this->session->set_flashdata('errors', '<p>Failed to create PIC. Please try again.</p>');
                }
            }
        }

        $data = [
            'title' => 'Add Client PIC',
            'active_menu' => 'client',
            'client' => $client
        ];

        $this->render('pic/create', $data);
    }

    public function edit($id)
    {
        $pic = $this->Client_pic_model->get_by_id($id);
        if (!$pic) {
            $this->session->set_flashdata('errors', '<p>PIC not found.</p>');
            redirect('client');
            return;
        }

        $client = $this->Client_model->get_by_id($pic->client_id);
        $account = null;
        if (!empty($pic->account_id)) {
            $account = $this->Account_model->get_by_id($pic->account_id);
        }

        if ($this->input->method() === 'post') {
            // Check if user requested to unlink the account
            if ($this->input->post('action') === 'unlink') {
                if ($account) {
                    $this->db->trans_start();
                    $this->Account_model->delete($account->id);
                    $this->Client_pic_model->update($id, ['account_id' => NULL]);
                    $this->db->trans_complete();

                    if ($this->db->trans_status() === TRUE) {
                        $this->session->set_flashdata('success', 'Login account unlinked and deleted successfully.');
                    } else {
                        $this->session->set_flashdata('errors', '<p>Failed to unlink account. Please try again.</p>');
                    }
                }
                redirect('pic/edit/' . $id);
                return;
            }

            $rules = [
                [
                    'field' => 'name',
                    'label' => 'PIC Name',
                    'rules' => 'trim|required|max_length[255]'
                ],
                [
                    'field' => 'position',
                    'label' => 'Position',
                    'rules' => 'trim|max_length[255]'
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
                    'label' => 'Username',
                    'rules' => 'trim|required|min_length[3]|max_length[50]'
                ];
                $rules[] = [
                    'field' => 'email',
                    'label' => 'Email',
                    'rules' => 'trim|required|valid_email|callback_pic_email_check[' . $account->id . ']'
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
                    'label' => 'Username',
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

                $pic_data = [
                    'name' => $this->input->post('name'),
                    'position' => $this->input->post('position') ?: null,
                    'is_active' => $this->input->post('is_active')
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
                        $pic_data['account_id'] = $account_id;
                    }
                }

                $this->Client_pic_model->update($id, $pic_data);

                $this->db->trans_complete();

                if ($this->db->trans_status() === TRUE) {
                    $this->session->set_flashdata('success', 'PIC updated successfully.');
                    redirect('pic?client_id=' . $pic->client_id);
                    return;
                } else {
                    $this->session->set_flashdata('errors', '<p>Failed to update PIC. Please try again.</p>');
                }
            }
        }

        $data = [
            'title' => 'Edit Client PIC',
            'active_menu' => 'client',
            'pic' => $pic,
            'client' => $client,
            'account' => $account
        ];

        $this->render('pic/edit', $data);
    }

    public function delete($id)
    {
        if ($this->input->method() !== 'post') {
            redirect('client');
        }

        $pic = $this->Client_pic_model->get_by_id($id);
        if (!$pic) {
            $this->session->set_flashdata('errors', '<p>PIC not found.</p>');
            redirect('client');
            return;
        }

        $client_id = $pic->client_id;

        $this->db->trans_start();

        if (!empty($pic->account_id)) {
            $this->Account_model->delete($pic->account_id);
        }

        $this->Client_pic_model->delete($id);

        $this->db->trans_complete();

        if ($this->db->trans_status() === TRUE) {
            $this->session->set_flashdata('success', 'PIC deleted successfully.');
        } else {
            $this->session->set_flashdata('errors', '<p>Failed to delete PIC. Please try again.</p>');
        }

        redirect('pic?client_id=' . $client_id);
    }

    public function pic_email_check($email, $account_id)
    {
        if ($this->Account_model->is_email_used($email, $account_id) > 0) {
            $this->form_validation->set_message([
                'pic_email_check' => 'Email has already been used'
            ]);
            return FALSE;
        }
        return TRUE;
    }
}
