<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Filter_keyword_model extends CI_Model
{
    public $table = 'filter_keywords';

    public function get_all()
    {
        $this->db->where('is_active', 1);
        return $this->db->get($this->table)->result();
    }

    public function get_all_admin()
    {
        return $this->db->get($this->table)->result();
    }

    public function get_by_id($id)
    {
        $this->db->where('id', $id);
        return $this->db->get($this->table)->row();
    }

    public function get_by_type($type)
    {
        $this->db->where('type', $type);
        $this->db->where('is_active', 1);
        return $this->db->get($this->table)->result();
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
}
