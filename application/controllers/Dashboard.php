<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Client_model');
        $this->load->model('Contract_model');
        $this->load->model('Campaign_model');
        $this->load->model('Ad_model');
        $this->load->model('Complaint_model');
        $this->load->model('Report_model');
    }

    public function index()
    {
        $role = $this->current_account['role'];
        $user_id = $this->current_account['id'];

        $client_id = null;
        $ae_id = null;

        if ($role === 'client') {
            $client = $this->Client_model->get_by_account_id($user_id);
            $client_id = $client ? $client->id : -1;
        } elseif ($role === 'ae') {
            $ae_id = $user_id;
        }

        $data = [
            'title' => 'Dashboard',
            'active_menu' => 'dashboard',
            'total_contracts_active' => $this->Contract_model->count_active($client_id, $ae_id),
            'total_campaigns_running' => $this->Campaign_model->count_running($client_id, $ae_id),
            'total_unconnected_ads' => in_array($role, ['ae', 'manajemen'])
                ? $this->Ad_model->count_unconnected()
                : 0,
        ];

        if ($role === 'client') {
            $complaints = $this->Complaint_model->get_all(['client_id' => $client_id]);
            $data['total_open_complaints'] = count(array_filter($complaints, function ($c) {
                return $c->status === 'open';
            }));
            $data['contracts_with_campaigns'] = $this->Contract_model->get_all_with_campaigns($client_id);
            $data['total_ads'] = $this->Ad_model->count_all_ads_by_client($client_id);

            $this->render('dashboard/client', $data);
            return;
        }

        if ($role === 'manajemen') {
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

            $data['filters']          = $filters;
            $data['clients']          = $this->Client_model->get_all();
            $data['contract_list']    = $contract_list;
            $data['total_contracts']  = $total_contracts;
            $data['total_value']      = $total_value;
            $data['total_approved']   = $total_approved;
            $data['total_terminated'] = $total_terminated;
            $data['total_pending']    = $total_pending;
            $data['total_unconnected_ads'] = $this->Ad_model->count_unconnected();

            $this->render('dashboard/manajemen', $data);
            return;
        }

        if ($role === 'ae') {
            $data['total_clients_handled'] = $this->Client_model->count_active($ae_id);
            $this->render('dashboard/ae', $data);
            return;
        }
        if ($role === 'superadmin') {
            $data['total_clients_active'] = $this->Client_model->count_active();
            $data['total_clients'] = $this->Client_model->count_total();
            $this->load->model('Cron_log_model');
            $data['cron_last_per_platform'] = $this->Cron_log_model->get_last_per_platform();
            $data['cron_recent'] = $this->Cron_log_model->get_latest(10);

            $this->render('dashboard/superadmin', $data);
            return;
        }

        $this->render('dashboard/superadmin', $data);
    }
}
