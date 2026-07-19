<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Account_model extends CI_Model
{
    public function get_all_with_roles($filters = [])
    {
        $this->db->select("accounts.*, roles.name AS role_name");
        $this->db->join("roles", "roles.id = accounts.role_id");

        if (!empty($filters['q'])) {
            $q = $this->db->escape_like_str($filters['q']);
            $this->db->group_start();
            $this->db->like("accounts.name", $q);
            $this->db->or_like("accounts.email", $q);
            $this->db->group_end();
        }

        if (!empty($filters['role_id'])) {
            $this->db->where("accounts.role_id", $filters['role_id']);
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $this->db->where("accounts.is_active", $filters['status']);
        }

        if (!empty($filters['exclude_id'])) {
            $this->db->where("accounts.id !=", $filters['exclude_id']);
        }

        if (!empty($filters['exclude_superadmin'])) {
            $this->db->where("roles.name !=", 'superadmin');
        }

        return $this->db->get("accounts")->result();
    }

    public function get_by_id($id)
    {
        $this->db->select("accounts.*, roles.name AS role_name");
        $this->db->join("roles", "roles.id = accounts.role_id", 'left');
        $this->db->where("accounts.id", $id);
        return $this->db->get("accounts")->row();
    }

    public function get_by_email($email)
    {
        $this->db->select("accounts.*, roles.name AS role_name");
        $this->db->join("roles", "roles.id = accounts.role_id");
        $this->db->where('email', $email);
        $this->db->where('is_active', 1);
        return $this->db->get("accounts")->row();
    }

    public function insert($data)
    {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        $this->db->insert("accounts", $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }

        $this->db->where('id', $id);
        $this->db->update("accounts", $data);
        return $this->db->affected_rows();
    }

    public function delete($id)
    {
        // check for dependencies before deleting to avoid foreign key constraint errors
        $this->db->where('ae_id', $id);
        $client_count = $this->db->count_all_results('clients');

        $this->db->where('account_id', $id);
        $pic_count = $this->db->count_all_results('client_pics');

        if ($client_count > 0 || $pic_count > 0) {
            return 'constraint_error';
        }

        $this->db->where('id', $id);
        $this->db->delete("accounts");

        return $this->db->affected_rows();
    }

    public function is_ae_exist_by_id($id)
    {
        $this->db->select("accounts.id");
        $this->db->join("roles", "roles.id = accounts.role_id");
        $this->db->where("accounts.id", $id);
        $this->db->where("roles.name", 'ae');
        $this->db->where("accounts.is_active", 1);
        return $this->db->get("accounts")->num_rows();
    }

    public function get_all_ae()
    {
        $this->db->select("accounts.*");
        $this->db->join("roles", "roles.id = accounts.role_id");
        $this->db->where("roles.name", 'ae');
        $this->db->where("accounts.is_active", 1);
        return $this->db->get("accounts")->result();
    }

    public function is_email_used($email, $id)
    {
        if ($id) {
            $this->db->where('id !=', $id);
        }

        $this->db->where('email', $email);
        return $this->db->get("accounts")->num_rows();
    }

    public function set_reset_token($email, $token, $expired_at)
    {
        $this->db->where('email', $email);
        $this->db->update("accounts", [
            'reset_token' => $token,
            'reset_token_expired' => $expired_at
        ]);
        return $this->db->affected_rows() > 0;
    }

    public function get_by_reset_token($token)
    {
        $this->db->where('reset_token', $token);
        $this->db->where('reset_token_expired >', date('Y-m-d H:i:s'));
        $this->db->where('is_active', 1);
        return $this->db->get("accounts")->row();
    }

    public function clear_reset_token($id)
    {
        $this->db->where('id', $id);
        $this->db->update("accounts", [
            'reset_token' => NULL,
            'reset_token_expired' => NULL
        ]);
    }
}
