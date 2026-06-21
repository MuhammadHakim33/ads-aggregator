<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Contract_model extends CI_Model
{
    public $table = 'contracts';

    public function __construct()
    {
        parent::__construct();
        // // Dynamically verify and add deleted_at if missing (safety check)
        // if ($this->db->table_exists($this->table) && !$this->db->field_exists('deleted_at', $this->table)) {
        //     $this->load->dbforge();
        //     $fields = [
        //         'deleted_at' => [
        //             'type' => 'TIMESTAMP',
        //             'null' => TRUE,
        //             'default' => NULL
        //         ]
        //     ];
        //     $this->dbforge->add_column($this->table, $fields);
        // }
    }

    public function get_all($filters = [])
    {
        $this->db->select('contracts.*, clients.company_name as client_name');
        $this->db->from($this->table);
        $this->db->join('clients', 'clients.id = contracts.client_id', 'inner');
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
        $this->db->from($this->table);
        $this->db->join('clients', 'clients.id = contracts.client_id', 'inner');
        $this->db->where('contracts.deleted_at', NULL);
        $this->db->where('contracts.terminated_at', NULL);
        $this->db->where('contracts.end_date >=', date('Y-m-d'));
        $this->db->order_by('contracts.created_at', 'DESC');
        return $this->db->get()->result();
    }

    public function get_by_id($id)
    {
        $this->db->select('contracts.*, clients.company_name as client_name');
        $this->db->from($this->table);
        $this->db->join('clients', 'clients.id = contracts.client_id', 'inner');
        $this->db->where('contracts.id', $id);
        $this->db->where('contracts.deleted_at', NULL);
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
        $data = [
            'deleted_at' => date('Y-m-d H:i:s')
        ];
        $this->db->where('id', $id);
        $this->db->update($this->table, $data);
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
        return $this->db->count_all_results($this->table) === 0;
    }

    public function count_active()
    {
        $this->db->where('deleted_at', NULL);
        $this->db->where('terminated_at', NULL);
        return $this->db->count_all_results($this->table);
    }

    public function count_total()
    {
        $this->db->where('deleted_at', NULL);
        return $this->db->count_all_results($this->table);
    }
}
