<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Client_model extends CI_Model
{
    public $table = 'clients';
    // private $table_contents = 'ad_contents';

    public function get_all($filters = [])
    {
        $this->db->select('clients.*, ae_acc.name as ae_name, (SELECT COUNT(*) FROM client_pics WHERE client_pics.client_id = clients.id) as pic_count');
        $this->db->from($this->table);
        $this->db->join('accounts ae_acc', 'ae_acc.id = clients.ae_id', 'left');
        $this->db->where('clients.deleted_at', NULL);

        if (!empty($filters['q'])) {
            $q = $this->db->escape_like_str($filters['q']);
            $this->db->group_start();
            $this->db->like('clients.company_name', $q);
            $this->db->or_where("EXISTS (SELECT 1 FROM client_pics WHERE client_pics.client_id = clients.id AND client_pics.name LIKE '%" . $q . "%')");
            $this->db->group_end();
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $this->db->where('clients.is_active', $filters['status']);
        }

        if (!empty($filters['ae_id'])) {
            $this->db->where('clients.ae_id', $filters['ae_id']);
        }

        return $this->db->get()->result();
    }

    public function get_by_id($id)
    {
        $this->db->where('id', $id);
        $this->db->where('deleted_at', NULL);
        return $this->db->get($this->table)->row();
    }

    public function get_by_account_id($account_id)
    {
        $this->db->select('clients.*');
        $this->db->from($this->table);
        $this->db->join('client_pics', 'client_pics.client_id = clients.id');
        $this->db->where('client_pics.account_id', $account_id);
        $this->db->where('clients.deleted_at', NULL);
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

    public function count_active()
    {
        $this->db->where('is_active', 1);
        $this->db->where('deleted_at', NULL);
        return $this->db->count_all_results($this->table);
    }

    public function count_total()
    {
        $this->db->where('deleted_at', NULL);
        return $this->db->count_all_results($this->table);
    }
}