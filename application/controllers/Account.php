<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Account extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Account_model');
        $this->load->model('Role_model');

        if ($this->router->fetch_method() !== 'profile') {
            $this->require_role('superadmin', 'manajemen');
        }
    }

    public function index()
    {
        $filters = [
            'q' => $this->input->get('q'),
            'role_id' => $this->input->get('role_id'),
            'status' => $this->input->get('status'),
            'exclude_id' => $this->current_account['id']
        ];

        if ($this->current_account['role'] === 'manajemen') {
            $filters['exclude_superadmin'] = TRUE;
        }

        $roles = $this->Role_model->get_all();
        if ($this->current_account['role'] === 'manajemen') {
            $roles = array_filter($roles, function ($r) {
                return $r->name !== 'superadmin';
            });
        }

        $data = [
            'title' => 'Account',
            'active_menu' => 'account',
            'filters' => $filters,
            'accounts' => $this->Account_model->get_all_with_roles($filters),
            'roles' => $roles
        ];

        $this->render('account/index', $data);
    }

    public function profile()
    {
        $id = $this->session->userdata('id');
        $account = $this->Account_model->get_by_id($id);

        if (!$account) {
            $this->session->set_flashdata('errors', 'Account not found.');
            redirect('dashboard');
            return;
        }

        if ($this->input->method() === 'post') {
            $section = $this->input->post('section');

            if ($section === 'info') {
                $this->form_validation->set_rules([
                    [
                        'field' => 'name',
                        'label' => 'Name',
                        'rules' => 'trim|required|min_length[3]|max_length[50]'
                    ],
                    [
                        'field' => 'email',
                        'label' => 'Email',
                        'rules' => 'trim|required|valid_email|callback_email_check[' . $id . ']'
                    ],
                ]);

                if ($this->form_validation->run() === TRUE) {
                    $this->Account_model->update($id, [
                        'name' => $this->input->post('name'),
                        'email' => strtolower($this->input->post('email')),
                    ]);

                    $this->session->set_userdata('name', $this->input->post('name'));
                    $this->session->set_flashdata('success', 'Profile information updated successfully.');
                    redirect('account/profile');
                    return;
                }

            } elseif ($section === 'password') {
                $this->form_validation->set_rules([
                    [
                        'field' => 'password',
                        'label' => 'Password',
                        'rules' => 'trim|required|min_length[6]'
                    ],
                    [
                        'field' => 'password_confirm',
                        'label' => 'Confirm Password',
                        'rules' => 'trim|required|matches[password]'
                    ]
                ]);

                if ($this->form_validation->run() === TRUE) {
                    $this->Account_model->update($id, [
                        'password' => $this->input->post('password'),
                    ]);

                    $this->session->set_flashdata('success', 'Password updated successfully.');
                    redirect('account/profile');
                    return;
                }
            }
        }

        $data = [
            'title' => 'My Profile',
            'account' => $account,
        ];

        $this->render('account/profile', $data);
    }


    public function create()
    {
        if ($this->input->method() === 'post') {

            $this->form_validation->set_rules($this->_create_rules());

            if ($this->form_validation->run() === TRUE) {
                $insert_id = $this->Account_model->insert([
                    'name' => $this->input->post('name'),
                    'email' => strtolower($this->input->post('email')),
                    'password' => $this->input->post('password'),
                    'role_id' => $this->input->post('role_id'),
                    'is_active' => TRUE
                ]);

                if ($insert_id) {
                    $this->session->set_flashdata('success', 'Account created successfully.');
                    redirect('account');
                    return;
                } else {
                    $this->session->set_flashdata('errors', '<p>Failed to create account. Please try again.</p>');
                }
            }
        }

        $roles = $this->Role_model->get_all();
        $roles = array_filter($roles, function ($r) {
            if ($r->name === 'client') {
                return FALSE;
            }
            if ($this->current_account['role'] === 'manajemen' && $r->name === 'superadmin') {
                return FALSE;
            }
            return TRUE;
        });

        $data = [
            'title' => 'Create Account',
            'active_menu' => 'account',
            'roles' => $roles
        ];

        $this->render('account/create', $data);
    }

    public function edit($id)
    {
        // check account exists
        $account = $this->Account_model->get_by_id($id);
        if (!$account) {
            $this->session->set_flashdata('errors', '<p>Account not found.</p>');
            redirect('account');
            return;
        }

        // prevent manajemen from editing superadmin accounts
        if ($this->current_account['role'] === 'manajemen' && $account->role_name === 'superadmin') {
            $this->session->set_flashdata('errors', '<p>You are not allowed to edit superadmin accounts.</p>');
            redirect('account');
            return;
        }

        if ($this->input->method() === 'post') {

            $this->form_validation->set_rules($this->_update_rules($id));

            if ($this->form_validation->run() === TRUE) {
                $data = [
                    'name' => $this->input->post('name'),
                    'email' => strtolower($this->input->post('email')),
                    'role_id' => $this->input->post('role_id'),
                    'is_active' => $this->input->post('is_active'),
                ];

                // only update if password is not empty
                $password = $this->input->post('password');
                if ($password !== null && $password !== '') {
                    $data['password'] = $password;
                }

                // update account
                $updated = $this->Account_model->update($id, $data);

                if ($updated) {
                    $this->session->set_flashdata('success', 'Account updated successfully.');
                    redirect('account');
                    return;
                } else {
                    $this->session->set_flashdata('errors', '<p>Failed to update account. Please try again.</p>');
                }
            }
        }

        $roles = $this->Role_model->get_all();
        $roles = array_filter($roles, function ($r) {
            if ($r->name === 'client') {
                return FALSE;
            }
            if ($this->current_account['role'] === 'manajemen' && $r->name === 'superadmin') {
                return FALSE;
            }
            return TRUE;
        });

        $data = [
            'title' => 'Edit Account',
            'active_menu' => 'account',
            'account' => $account,
            'roles' => $roles
        ];

        $this->render('account/edit', $data);
    }


    public function delete($id)
    {
        if ($this->input->method() !== 'post') {
            redirect('account');
            return;
        }

        // check account exists
        $account = $this->Account_model->get_by_id($id);
        if (!$account) {
            $this->session->set_flashdata('errors', '<p>Account not found.</p>');
            redirect('account');
            return;
        }

        // prevent manajemen from deleting superadmin accounts
        if ($this->current_account['role'] === 'manajemen' && $account->role_name === 'superadmin') {
            $this->session->set_flashdata('errors', '<p>You are not allowed to delete superadmin accounts.</p>');
            redirect('account');
            return;
        }

        // prevent superadmin from deleting their own account
        if ((int) $id === (int) $this->session->userdata('id')) {
            $this->session->set_flashdata('errors', '<p>You cannot delete your own account.</p>');
            redirect('account');
            return;
        }

        $deleted = $this->Account_model->delete($id);

        if ($deleted) {
            $this->session->set_flashdata('success', 'Account deleted successfully.');
        } else {
            $this->session->set_flashdata('errors', '<p>Failed to delete account. Please try again.</p>');
        }

        redirect('account');
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
                'field' => 'role_id',
                'label' => 'Role',
                'rules' => 'trim|required|callback_role_check'
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
                'field' => 'role_id',
                'label' => 'Role',
                'rules' => 'trim|required|callback_role_check'
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
            $this->form_validation->set_message([
                'email_check' => 'Email has already been used'
            ]);
            return FALSE;
        }

        return TRUE;
    }

    public function role_check($role_id)
    {
        if ($this->Role_model->is_exist_by_id($role_id) == 0) {
            $this->form_validation->set_message([
                'role_check' => 'The selected Role does not exist'
            ]);
            return FALSE;
        }

        // prevent manajemen from assigning superadmin role
        $role = $this->Role_model->get_by_id($role_id);
        if ($this->current_account['role'] === 'manajemen' && $role && $role->name === 'superadmin') {
            $this->form_validation->set_message([
                'role_check' => 'You are not allowed to assign the superadmin role.'
            ]);
            return FALSE;
        }

        return TRUE;
    }
}
