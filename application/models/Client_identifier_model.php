<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Client_identifier_model extends CI_Model 
{
    public $table = 'client_identifiers';

    public function get_by_client_id($client_id)
    {
        $this->db->where('client_id', $client_id);
        return $this->db->get($this->table)->result();
    }

    public function get_by_id($id)
    {
        $this->db->where('id', $id);
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
        $this->db->update($this->table, $data);
        return $this->db->affected_rows();
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete($this->table);
        return $this->db->affected_rows();
    }

    public function is_exist_by_id($id)
    {
        $this->db->where('id', $id);
        return $this->db->get($this->table)->num_rows();
    }

    public function check_duplicate($client_id, $platform, $identifier, $exclude_id = null)
    {
        $this->db->where('client_id', $client_id);
        $this->db->where('platform', $platform);
        $this->db->where('identifier', $identifier);
        
        if ($exclude_id !== null) {
            $this->db->where('id !=', $exclude_id);
        }
        
        return $this->db->get($this->table)->num_rows() > 0;
    }
}
