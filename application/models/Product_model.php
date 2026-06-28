<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Product_model extends CI_Model
{
    public $table = 'products';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_all_active()
    {
        $this->db->where('is_active', 1);
        $this->db->order_by('category', 'ASC');
        $this->db->order_by('name', 'ASC');
        return $this->db->get($this->table)->result();
    }

    public function get_by_id($id)
    {
        $this->db->where('id', $id);
        $this->db->where('is_active', 1);
        return $this->db->get($this->table)->row();
    }
}
