<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Platform_model extends CI_Model
{
    private $table = 'platforms';

    public function get_all()
    {
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

    public function is_name_used($name)
    {
        $this->db->where('name', $name);
        return $this->db->get($this->table)->num_rows();
    }
}