<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Account_model extends CI_Model
{
    private $table = 'accounts';
    private $table_roles = 'roles';

    public function get_all_with_roles($filters = [])
    {
        $this->db->select("{$this->table}.*, {$this->table_roles}.name AS role_name");
        $this->db->join("{$this->table_roles}", "{$this->table_roles}.id = {$this->table}.role_id");

        if (!empty($filters['q'])) {
            $q = $this->db->escape_like_str($filters['q']);
            $this->db->group_start();
            $this->db->like("{$this->table}.name", $q);
            $this->db->or_like("{$this->table}.email", $q);
            $this->db->group_end();
        }

        if (!empty($filters['role_id'])) {
            $this->db->where("{$this->table}.role_id", $filters['role_id']);
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $this->db->where("{$this->table}.is_active", $filters['status']);
        }

        return $this->db->get($this->table)->result();
    }

    public function get_by_id($id)
    {
        $this->db->where('id', $id);
        return $this->db->get($this->table)->row();
    }

    public function get_by_email($email)
    {
        $this->db->select("{$this->table}.*, {$this->table_roles}.name AS role_name");
        $this->db->join("{$this->table_roles}", "{$this->table_roles}.id = {$this->table}.role_id");
        $this->db->where('email', $email);
        $this->db->where('is_active', 1);
        return $this->db->get($this->table)->row();
    }

    public function insert($data)
    {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        $this->db->insert($this->table, $data);
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
        $this->db->update($this->table, $data);
        return $this->db->affected_rows();
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete($this->table);
        return $this->db->affected_rows();
    }

    // public function is_exist_by_id($id)
    // {
    //     $this->db->where('id', $id);
    //     $this->db->where('deleted_at', NULL);
    //     return $this->db->get($this->table)->num_rows();
    // }

    public function is_ae_exist_by_id($id)
    {
        $this->db->select("{$this->table}.id");
        $this->db->join("{$this->table_roles}", "{$this->table_roles}.id = {$this->table}.role_id");
        $this->db->where("{$this->table}.id", $id);
        $this->db->where("{$this->table_roles}.name", 'ae');
        $this->db->where("{$this->table}.is_active", 1);
        return $this->db->get($this->table)->num_rows();
    }

    public function get_all_ae()
    {
        $this->db->select("{$this->table}.*");
        $this->db->join("{$this->table_roles}", "{$this->table_roles}.id = {$this->table}.role_id");
        $this->db->where("{$this->table_roles}.name", 'ae');
        $this->db->where("{$this->table}.is_active", 1);
        return $this->db->get($this->table)->result();
    }

    public function is_email_used($email, $id)
    {
        if ($id) {
            $this->db->where('id !=', $id);
        }

        $this->db->where('email', $email);
        return $this->db->get($this->table)->num_rows();
    }
}
