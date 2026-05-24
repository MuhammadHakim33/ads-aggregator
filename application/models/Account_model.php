<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Account_model extends CI_Model
{
    private $table = 'accounts';

    public function get_all()
    {
        $this->db->where('deleted_at', NULL);
        return $this->db->get($this->table)->result();
    }

    public function get_by_id($id)
    {
        $this->db->where('id', $id);
        $this->db->where('deleted_at', NULL);
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
        $this->db->where('deleted_at', NULL);
        $this->db->update($this->table, $data);
        return $this->db->affected_rows();
    }

    public function delete($id)
    {
        $data = array(
            'is_active' => 0,
            'deleted_at' => date('Y-m-d H:i:s')
        );
        $this->db->where('id', $id);
        $this->db->update($this->table, $data);
        return $this->db->affected_rows();
    }

    public function is_exist_by_id($id)
    {
        $this->db->where('id', $id);
        $this->db->where('deleted_at', NULL);
        return $this->db->get($this->table)->num_rows();
    }

    public function is_ae_exist_by_id($id)
    {
        $this->db->where('id', $id);
        $this->db->where('role', 'ae');
        $this->db->where('is_active', 1);
        $this->db->where('deleted_at', NULL);
        return $this->db->get($this->table)->num_rows();
    }

    public function get_all_ae()
    {
        $this->db->where('role', 'ae');
        $this->db->where('is_active', 1);
        $this->db->where('deleted_at', NULL);
        return $this->db->get($this->table)->result();
    }

    public function is_email_used($email, $id)
    {
        if ($id) {
            $this->db->where('id !=', $id);
        }

        $this->db->where('email', $email);
        $this->db->where('deleted_at', NULL);
        return $this->db->get($this->table)->num_rows();
    }
}
