<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Filter_keyword_model extends CI_Model
{

    public function get_all()
    {
        $this->db->where('is_active', 1);
        return $this->db->get('filter_keywords')->result();
    }

    public function get_by_id($id)
    {
        $this->db->where('id', $id);
        return $this->db->get('filter_keywords')->row();
    }

    public function get_by_type($type)
    {
        $this->db->where('type', $type);
        $this->db->where('is_active', 1);
        return $this->db->get('filter_keywords')->result();
    }

    public function get_by_platform($platform)
    {
        $this->db->where('platform', $platform);
        $this->db->where('is_active', 1);
        return $this->db->get('filter_keywords')->result();
    }

    public function get_by_platform_admin($platform)
    {
        $this->db->where('platform', $platform);
        return $this->db->get('filter_keywords')->result();
    }

    public function get_by_platforms_admin(array $platforms)
    {
        $this->db->where_in('platform', $platforms);
        return $this->db->get('filter_keywords')->result();
    }

    public function insert($data)
    {
        $this->db->insert('filter_keywords', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update('filter_keywords', $data);
        return $this->db->affected_rows();
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('filter_keywords');
        return $this->db->affected_rows();
    }

    public function is_exist_by_id($id)
    {
        $this->db->where('id', $id);
        return $this->db->get('filter_keywords')->num_rows();
    }
}
