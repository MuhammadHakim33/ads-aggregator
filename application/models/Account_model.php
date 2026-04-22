<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Account_model extends CI_Model {

    public $table = 'accounts';

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
        return $this->db->update($this->table, $data);
    }

    public function delete($id)
    {
        $data = array(
            'is_active' => 0,
            'deleted_at' => date('Y-m-d H:i:s')
        );
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }

    public function is_exist_by_id($id)
    {
        $this->db->where('id', $id);
        $this->db->where('deleted_at', NULL);
        return $this->db->get($this->table)->num_rows();
    }
    
}
