<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Product_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_all($filters = [])
    {
        if (!empty($filters['q'])) {
            $this->db->like('name', $filters['q']);
        }

        if (isset($filters['category']) && $filters['category'] !== '') {
            $this->db->where('category', $filters['category']);
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $this->db->where('is_active', $filters['is_active']);
        }

        return $this->db->get('products')->result();
    }

    public function get_all_active()
    {
        $this->db->where('is_active', 1);
        return $this->db->get('products')->result();
    }

    public function get_by_id($id)
    {
        $this->db->where('id', $id);
        return $this->db->get('products')->row();
    }

    public function insert($data)
    {
        $this->db->insert('products', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('products', $data);
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('products');
    }
}
