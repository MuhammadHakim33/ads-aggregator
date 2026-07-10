<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Report extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->require_role('manajemen');
        $this->load->model('Report_model');
        $this->load->model('Client_model');
    }

    public function index()
    {
        // Default filter: current month
        $default_start = date('Y-m-01');
        $default_end   = date('Y-m-t');

        $filters = [
            'start_date' => $this->input->get('start_date') ?: $default_start,
            'end_date'   => $this->input->get('end_date')   ?: $default_end,
            'client_id'  => $this->input->get('client_id'),
            'status'     => $this->input->get('status'),
            'q'          => $this->input->get('q'),
        ];

        $contract_list = $this->Report_model->get_contract_report_list($filters);

        // Calculate summary stats from the list (no extra query needed)
        $total_contracts  = count($contract_list);
        $total_value      = 0;
        $total_approved   = 0;
        $total_terminated = 0;
        $total_pending    = 0;

        foreach ($contract_list as $c) {
            $total_value += (float) ($c->value ?? 0);

            if (!empty($c->terminated_at)) {
                $total_terminated++;
            } elseif ($c->status === 'approved') {
                $total_approved++;
            } elseif ($c->status === 'pending') {
                $total_pending++;
            }
        }

        $data = [
            'title'           => 'Contract Report',
            'active_menu'     => 'report',
            'filters'         => $filters,
            'clients'         => $this->Client_model->get_all(),
            'contract_list'   => $contract_list,
            'total_contracts' => $total_contracts,
            'total_value'     => $total_value,
            'total_approved'  => $total_approved,
            'total_terminated'=> $total_terminated,
            'total_pending'   => $total_pending,
        ];

        $this->render('report/index', $data);
    }
}
