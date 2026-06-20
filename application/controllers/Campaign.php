<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Campaign extends MY_Controller
{
    private $editing_id = null;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Campaign_model');
        $this->load->model('Contract_model');
        $this->load->model('Client_model');
    }

    public function index()
    {
        $filters = [
            'q' => $this->input->get('q'),
            'client_id' => $this->input->get('client_id'),
            'status' => $this->input->get('status')
        ];

        $data = [
            'title' => 'Campaigns',
            'active_menu' => 'campaign',
            'filters' => $filters,
            'campaigns' => $this->Campaign_model->get_all($filters),
            'clients' => $this->Client_model->get_all()
        ];

        $this->render('campaign/index', $data);
    }

    public function create()
    {
        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules([
                [
                    'field' => 'contract_id',
                    'label' => 'Contract',
                    'rules' => 'trim|required|integer|callback_contract_check'
                ],
                [
                    'field' => 'name',
                    'label' => 'Campaign Name',
                    'rules' => 'trim|required|max_length[255]'
                ],
                [
                    'field' => 'description',
                    'label' => 'Description',
                    'rules' => 'trim'
                ],
                [
                    'field' => 'start_date',
                    'label' => 'Start Date',
                    'rules' => 'trim|required|exact_length[10]|callback_valid_date'
                ],
                [
                    'field' => 'end_date',
                    'label' => 'End Date',
                    'rules' => 'trim|required|exact_length[10]|callback_valid_date|callback_date_range_check'
                ]
            ]);

            if ($this->form_validation->run() === TRUE) {
                $insert_id = $this->Campaign_model->insert([
                    'contract_id' => $this->input->post('contract_id'),
                    'name' => $this->input->post('name'),
                    'description' => $this->input->post('description') ?: null,
                    'start_date' => $this->input->post('start_date'),
                    'end_date' => $this->input->post('end_date'),
                    'is_active' => TRUE
                ]);

                if ($insert_id) {
                    $this->session->set_flashdata('success', 'Campaign created successfully.');
                    redirect('campaign');
                    return;
                } else {
                    $this->session->set_flashdata('errors', 'Failed to create campaign. Please try again.');
                }
            }
        }

        $data = [
            'title' => 'Create Campaign',
            'active_menu' => 'campaign',
            'contracts' => $this->Contract_model->get_all_for_select()
        ];

        $this->render('campaign/create', $data);
    }

    public function edit($id)
    {
        $campaign = $this->Campaign_model->get_by_id($id);
        if (!$campaign) {
            $this->session->set_flashdata('errors', 'Campaign not found.');
            redirect('campaign');
            return;
        }

        $this->editing_id = $id;

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules([
                [
                    'field' => 'contract_id',
                    'label' => 'Contract',
                    'rules' => 'trim|required|integer|callback_contract_check'
                ],
                [
                    'field' => 'name',
                    'label' => 'Campaign Name',
                    'rules' => 'trim|required|max_length[255]'
                ],
                [
                    'field' => 'description',
                    'label' => 'Description',
                    'rules' => 'trim'
                ],
                [
                    'field' => 'start_date',
                    'label' => 'Start Date',
                    'rules' => 'trim|required|exact_length[10]|callback_valid_date'
                ],
                [
                    'field' => 'end_date',
                    'label' => 'End Date',
                    'rules' => 'trim|required|exact_length[10]|callback_valid_date|callback_date_range_check'
                ]
            ]);

            if ($this->form_validation->run() === TRUE) {
                $this->Campaign_model->update($id, [
                    'contract_id' => $this->input->post('contract_id'),
                    'name' => $this->input->post('name'),
                    'description' => $this->input->post('description') ?: null,
                    'start_date' => $this->input->post('start_date'),
                    'end_date' => $this->input->post('end_date')
                ]);

                $this->session->set_flashdata('success', 'Campaign updated successfully.');
                redirect('campaign');
                return;
            }
        }

        $data = [
            'title' => 'Edit Campaign',
            'active_menu' => 'campaign',
            'campaign' => $campaign,
            'contracts' => $this->Contract_model->get_all_for_select()
        ];

        $this->render('campaign/edit', $data);
    }

    public function detail($id)
    {
        $campaign = $this->Campaign_model->get_campaign_with_ads_and_metrics($id);
        if (!$campaign) {
            $this->session->set_flashdata('errors', 'Campaign not found.');
            redirect('campaign');
            return;
        }

        $data = [
            'title'         => 'Campaign Detail',
            'active_menu'   => 'campaign',
            'campaign'      => $campaign,
        ];

        $this->render('campaign/detail', $data);
    }

    public function delete($id)
    {
        if ($this->input->method() !== 'post') {
            redirect('campaign');
            return;
        }

        $campaign = $this->Campaign_model->get_by_id($id);
        if (!$campaign) {
            $this->session->set_flashdata('errors', 'Campaign not found.');
            redirect('campaign');
            return;
        }

        // restrict deletion if active ads are linked
        if ($this->Campaign_model->has_ads($id)) {
            $this->session->set_flashdata('errors', 'Cannot delete campaign. It is linked to active ad contents.');
            redirect('campaign');
            return;
        }

        $deleted = $this->Campaign_model->delete($id);
        if ($deleted) {
            $this->session->set_flashdata('success', 'Campaign deleted successfully.');
        } else {
            $this->session->set_flashdata('errors', 'Failed to delete campaign. Please try again.');
        }

        redirect('campaign');
    }

    public function contract_check($contract_id)
    {
        $contract = $this->Contract_model->get_by_id($contract_id);
        if (!$contract) {
            $this->form_validation->set_message([
                'contract_check' => 'The selected Contract does not exist or is inactive.'
            ]);
            return FALSE;
        }
        return TRUE;
    }

    public function valid_date($date)
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        if ($d && $d->format('Y-m-d') === $date) {
            return TRUE;
        }

        $this->form_validation->set_message([
            'valid_date' => 'The {field} field must be in YYYY-MM-DD format.'
        ]);

        return FALSE;
    }

    public function date_range_check($end_date)
    {
        $start_date = $this->input->post('start_date');
        $contract_id = $this->input->post('contract_id');

        // check start_date <= end_date
        if (strtotime($end_date) < strtotime($start_date)) {
            $this->form_validation->set_message([
                'date_range_check' => 'The End Date must be equal to or after the Start Date.'
            ]);
            return FALSE;
        }

        // check must fall within the parent contract dates
        $contract = $this->Contract_model->get_by_id($contract_id);
        if ($contract) {
            // verify start date boundary
            if (strtotime($start_date) < strtotime($contract->start_date)) {
                $this->form_validation->set_message([
                    'date_range_check' => "Campaign Start Date cannot be earlier than Contract Start Date ({$contract->start_date})."
                ]);
                return FALSE;
            }

            // verify end date boundary (taking into account early termination if it exists)
            $contract_max_end = $contract->terminated_at ? date('Y-m-d', strtotime($contract->terminated_at)) : $contract->end_date;
            if (strtotime($end_date) > strtotime($contract_max_end)) {
                $this->form_validation->set_message([
                    'date_range_check' => "Campaign End Date cannot exceed Contract End Date ({$contract_max_end})."
                ]);
                return FALSE;
            }
        }

        return TRUE;
    }

    public function export($format, $id)
    {
        $this->load->library('Export_registry');
        
        $campaign = $this->Campaign_model->get_campaign_with_ads_and_metrics($id);
        if (!$campaign) {
            show_error('Campaign not found.', 404);
            return;
        }

        if (!$this->export_registry->has($format)) {
            show_error("Unknown export format: {$format}", 400);
            return;
        }

        $exporter = $this->make_exporter($format);
        $filename = 'report_campaign_' . $id . '_' . date('Ymd');
        $exporter->generate($campaign, $filename);
    }

    private function make_exporter($format)
    {
        $conf = $this->export_registry->configs()[$format];
        require_once $conf['class_path'];
        $class = $conf['class'];
        return new $class();
    }
}
