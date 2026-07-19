<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Report_model extends CI_Model
{
    public function get_contract_report_list($filters = [])
    {
        $this->db->select('contracts.*, clients.company_name as client_name');
        $this->db->from('contracts');
        $this->db->join('clients', 'clients.id = contracts.client_id', 'inner');
        // $this->db->join('accounts approver', 'approver.id = contracts.approved_by', 'left');
        $this->db->where('contracts.deleted_at', NULL);
        $this->db->where('contracts.terminated_at', NULL);
        $this->db->where('contracts.status', 'approved');

        if (!empty($filters['start_date'])) {
            $this->db->where('contracts.start_date >=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $this->db->where('contracts.start_date <=', $filters['end_date']);
        }

        if (!empty($filters['client_id'])) {
            $this->db->where('contracts.client_id', $filters['client_id']);
        }

        // if (!empty($filters['status'])) {
        //     if ($filters['status'] === 'terminated') {
        //         $this->db->where('contracts.terminated_at IS NOT NULL', NULL, FALSE);
        //     } else {
        //         $this->db->where('contracts.terminated_at', NULL);
        //         $this->db->where('contracts.status', $filters['status']);
        //     }
        // }

        if (!empty($filters['q'])) {
            $q = $this->db->escape_like_str($filters['q']);
            $this->db->group_start();
            $this->db->like('contracts.contract_number', $q);
            $this->db->or_like('clients.company_name', $q);
            $this->db->group_end();
        }

        $this->db->order_by('contracts.start_date', 'DESC');
        return $this->db->get()->result();
    }
}
