<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Client_model extends CI_Model 
{
    public $table = 'clients';
    private $table_contents = 'ad_contents';

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

    public function count_active()
    {
        $this->db->where('is_active', 1);
        $this->db->where('deleted_at', NULL);
        return $this->db->count_all_results($this->table);
    }

    public function get_clients_summary()
    {
        $query = $this->db->query("
            SELECT 
                c.id, 
                c.company_name, 
                c.pic_name,
                COUNT(a.id) as total_ads,
                SUM(CASE WHEN a.is_active = 1 THEN 1 ELSE 0 END) as active_ads,
                GROUP_CONCAT(DISTINCT a.platform SEPARATOR ',') as platforms
            FROM clients c
            LEFT JOIN {$this->table_contents} a ON c.id = a.client_id
            WHERE c.deleted_at IS NULL
            GROUP BY c.id
            ORDER BY c.company_name ASC
        ");

        return $query->result();
    }

    public function assign_client($ad_content_id, $client_id)
    {
        $this->db->where('id', $ad_content_id);
        $this->db->update($this->table_contents, ['client_id' => (int)$client_id]);
        return $this->db->affected_rows();
    }
}