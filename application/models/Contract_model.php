<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Contract_model extends CI_Model
{
    public function get_all($filters = [])
    {
        $this->db->select('contracts.*, clients.company_name as client_name, approver.name as approver_name');
        $this->db->from('contracts');
        $this->db->join('clients', 'clients.id = contracts.client_id', 'inner');
        $this->db->join('accounts approver', 'approver.id = contracts.approved_by', 'left');
        $this->db->where('contracts.deleted_at', NULL);

        if (!empty($filters['q'])) {
            $q = $this->db->escape_like_str($filters['q']);
            $this->db->group_start();
            $this->db->like('contracts.contract_number', $q);
            $this->db->or_like('clients.company_name', $q);
            $this->db->group_end();
        }

        if (!empty($filters['client_id'])) {
            $this->db->where('contracts.client_id', $filters['client_id']);
        }

        $this->db->order_by('contracts.created_at', 'DESC');
        return $this->db->get()->result();
    }

    public function get_all_for_select()
    {
        $this->db->select('contracts.*, clients.company_name as client_name');
        $this->db->from('contracts');
        $this->db->join('clients', 'clients.id = contracts.client_id', 'inner');
        $this->db->where('contracts.deleted_at', NULL);
        $this->db->where('contracts.status', 'approved');
        $this->db->where('contracts.terminated_at', NULL);
        $this->db->where('contracts.end_date >=', date('Y-m-d'));
        $this->db->order_by('contracts.created_at', 'DESC');
        return $this->db->get()->result();
    }

    public function get_by_id($id)
    {
        $this->db->select('contracts.*, clients.company_name as client_name, approver.name as approver_name');
        $this->db->from('contracts');
        $this->db->join('clients', 'clients.id = contracts.client_id', 'inner');
        $this->db->join('accounts approver', 'approver.id = contracts.approved_by', 'left');
        $this->db->where('contracts.id', $id);
        $this->db->where('contracts.deleted_at', NULL);
        return $this->db->get()->row();
    }

    public function insert($data)
    {
        $this->db->insert('contracts', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->where('deleted_at', NULL);
        $this->db->update('contracts', $data);
        return $this->db->affected_rows();
    }

    public function delete($id)
    {
        $data = [
            'deleted_at' => date('Y-m-d H:i:s')
        ];
        $this->db->where('id', $id);
        $this->db->update('contracts', $data);
        return $this->db->affected_rows();
    }

    public function has_campaigns($id)
    {
        $this->db->where('contract_id', $id);
        $this->db->where('is_active', 1);
        return $this->db->count_all_results('campaigns') > 0;
    }

    public function is_contract_number_unique($contract_number, $exclude_id = null)
    {
        $this->db->where('contract_number', $contract_number);
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        $this->db->where('deleted_at', NULL);
        return $this->db->count_all_results('contracts') === 0;
    }

    public function count_active($client_id = null, $ae_id = null)
    {
        $this->db->where('contracts.deleted_at', NULL);
        $this->db->where('contracts.terminated_at', NULL);
        $this->db->where('contracts.status', 'approved');
        $this->db->where('contracts.start_date <=', date('Y-m-d'));
        $this->db->where('contracts.end_date >=', date('Y-m-d'));
        if ($client_id) {
            $this->db->where('contracts.client_id', $client_id);
        }
        if ($ae_id) {
            $this->db->join('clients', 'clients.id = contracts.client_id', 'inner');
            $this->db->where('clients.ae_id', $ae_id);
        }
        return $this->db->count_all_results('contracts');
    }

    public function get_items($contract_id)
    {
        $this->db->select('contract_items.*, products.name as product_name, products.category as product_category, products.price_model as product_price_model');
        $this->db->from('contract_items');
        $this->db->join('products', 'products.id = contract_items.product_id', 'inner');
        $this->db->where('contract_items.contract_id', $contract_id);
        return $this->db->get()->result();
    }

    public function insert_item($data)
    {
        return $this->db->insert('contract_items', $data);
    }

    public function delete_items($contract_id)
    {
        $this->db->where('contract_id', $contract_id);
        return $this->db->delete('contract_items');
    }

    public function get_all_with_campaigns($client_id, $limit = null)
    {
        // fetch all contracts for this client
        $this->db->select('contracts.*, clients.company_name as client_name, approver.name as approver_name');
        $this->db->from('contracts');
        $this->db->join('clients', 'clients.id = contracts.client_id', 'inner');
        $this->db->join('accounts approver', 'approver.id = contracts.approved_by', 'left');
        $this->db->where('contracts.deleted_at', NULL);
        $this->db->where('contracts.client_id', $client_id);
        $this->db->order_by('contracts.created_at', 'DESC');
        if ($limit !== null) {
            $this->db->limit($limit);
        }
        $contracts = $this->db->get()->result();

        if (empty($contracts)) {
            return [];
        }

        // collect contract IDs
        $contract_ids = array_column($contracts, 'id');

        // fetch all campaigns belonging to these contracts
        $this->db->select('campaigns.*, contracts.contract_number');
        $this->db->from('campaigns');
        $this->db->join('contracts', 'contracts.id = campaigns.contract_id', 'inner');
        $this->db->where('campaigns.deleted_at', NULL);
        $this->db->where_in('campaigns.contract_id', $contract_ids);
        $this->db->order_by('campaigns.start_date', 'ASC');
        $campaigns = $this->db->get()->result();

        // index campaigns by contract_id
        $campaigns_map = [];
        foreach ($campaigns as $c) {
            $campaigns_map[$c->contract_id][] = $c;
        }

        // attach campaigns to each contract
        foreach ($contracts as &$contract) {
            $contract->campaigns = $campaigns_map[$contract->id] ?? [];
        }

        return $contracts;
    }

    public function get_expiring($ae_id = null, $days = 30)
    {
        $today = date('Y-m-d');
        $limit_date = date('Y-m-d', strtotime("+{$days} days"));

        $this->db->select('contracts.*, clients.company_name as client_name');
        $this->db->from('contracts');
        $this->db->join('clients', 'clients.id = contracts.client_id', 'inner');
        $this->db->where('contracts.deleted_at', NULL);
        $this->db->where('contracts.terminated_at', NULL);
        $this->db->where('contracts.status', 'approved');
        $this->db->where('contracts.end_date >=', $today);
        $this->db->where('contracts.end_date <=', $limit_date);

        if ($ae_id) {
            $this->db->where('clients.ae_id', $ae_id);
        }

        $this->db->order_by('contracts.end_date', 'ASC');
        $contracts = $this->db->get()->result();

        if (empty($contracts)) {
            return [];
        }

        $contract_ids = array_column($contracts, 'id');

        $this->db->select('campaigns.*, contracts.contract_number');
        $this->db->from('campaigns');
        $this->db->join('contracts', 'contracts.id = campaigns.contract_id', 'inner');
        $this->db->where('campaigns.deleted_at', NULL);
        $this->db->where_in('campaigns.contract_id', $contract_ids);
        $this->db->order_by('campaigns.start_date', 'ASC');
        $campaigns = $this->db->get()->result();

        $campaigns_map = [];
        foreach ($campaigns as $c) {
            $campaigns_map[$c->contract_id][] = $c;
        }

        foreach ($contracts as &$contract) {
            $contract->campaigns = $campaigns_map[$contract->id] ?? [];
        }

        return $contracts;
    }
}
