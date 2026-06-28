<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Complaint_model extends CI_Model
{
    private $table = 'complaints';

    public function get_all($filters = [])
    {
        $this->db->select("c.*, ac.title AS ad_title, ac.platform, camp.name AS campaign_name, cl.company_name AS client_name");
        $this->db->from($this->table . " c");
        $this->db->join("ad_contents ac", "ac.id = c.ad_content_id");
        $this->db->join("campaigns camp", "camp.id = ac.campaign_id");
        $this->db->join("contracts cont", "cont.id = camp.contract_id");
        $this->db->join("clients cl", "cl.id = cont.client_id");

        if (!empty($filters['client_id'])) {
            $this->db->where('cont.client_id', $filters['client_id']);
        }

        if (!empty($filters['ae_id'])) {
            $this->db->where('cl.ae_id', $filters['ae_id']);
        }

        if (!empty($filters['status'])) {
            $this->db->where('c.status', $filters['status']);
        }

        if (!empty($filters['q'])) {
            $q = $this->db->escape_like_str($filters['q']);
            $this->db->group_start();
            $this->db->like('c.subject', $q);
            $this->db->or_like('c.description', $q);
            $this->db->or_like('ac.title', $q);
            $this->db->group_end();
        }

        $this->db->order_by('c.created_at', 'DESC');
        return $this->db->get()->result();
    }

    public function get_by_id($id)
    {
        $this->db->select("c.*, ac.title AS ad_title, ac.platform, ac.content_identifier, camp.id AS campaign_id, camp.name AS campaign_name, cl.id AS client_id, cl.company_name AS client_name, cl.ae_id");
        $this->db->from($this->table . " c");
        $this->db->join("ad_contents ac", "ac.id = c.ad_content_id");
        $this->db->join("campaigns camp", "camp.id = ac.campaign_id");
        $this->db->join("contracts cont", "cont.id = camp.contract_id");
        $this->db->join("clients cl", "cl.id = cont.client_id");
        $this->db->where('c.id', $id);
        return $this->db->get()->row();
    }

    public function insert($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }
}
