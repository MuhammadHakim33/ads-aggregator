<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Client_model extends CI_Model 
{
    public $table = 'clients';

    public function get_all()
    {
        $this->db->select('clients.*, accounts.name as ae_name');
        $this->db->from($this->table);
        $this->db->join('accounts', 'accounts.id = clients.ae_id AND accounts.deleted_at IS NULL', 'left');
        $this->db->where('clients.deleted_at', NULL);
        return $this->db->get()->result();
    }

    public function get_by_id($id)
    {
        $this->db->where('id', $id);
        $this->db->where('deleted_at', NULL);
        return $this->db->get($this->table)->row();
    }

    public function insert($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
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
}