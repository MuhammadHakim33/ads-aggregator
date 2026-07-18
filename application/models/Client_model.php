<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Client_model extends CI_Model
{
    public function get_all($filters = [])
    {
        $this->db->select('clients.*, ae_acc.name as ae_name, (SELECT COUNT(*) FROM client_pics WHERE client_pics.client_id = clients.id) as pic_count');
        $this->db->from('clients');
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
        return $this->db->get('clients')->row();
    }

    public function get_by_account_id($account_id)
    {
        $this->db->select('clients.*');
        $this->db->from('clients');
        $this->db->join('client_pics', 'client_pics.client_id = clients.id');
        $this->db->where('client_pics.account_id', $account_id);
        $this->db->where('clients.deleted_at', NULL);
        return $this->db->get()->row();
    }

    public function insert($data)
    {
        $this->db->insert('clients', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->where('deleted_at', NULL);
        $this->db->update('clients', $data);
        return $this->db->affected_rows();
    }

    public function delete($id)
    {
        $data = array(
            'is_active' => 0,
            'deleted_at' => date('Y-m-d H:i:s')
        );
        $this->db->where('id', $id);
        $this->db->update('clients', $data);
        return $this->db->affected_rows();
    }

    public function count_active($ae_id = null)
    {
        $this->db->where('is_active', 1);
        $this->db->where('deleted_at', NULL);
        if ($ae_id) {
            $this->db->where('ae_id', $ae_id);
        }
        return $this->db->count_all_results('clients');
    }

    public function count_total($ae_id = null)
    {
        $this->db->where('deleted_at', NULL);
        if ($ae_id) {
            $this->db->where('ae_id', $ae_id);
        }
        return $this->db->count_all_results('clients');
    }
}