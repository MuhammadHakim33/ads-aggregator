<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ads extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Ad_model');
        $this->load->model('Client_model');
        $this->load->library('Platform_registry');
        $this->load->library('Export_registry');
    }

    public function index()
    {
        $this->load->model('Campaign_model');

        if ($this->input->method() === 'post') {
            $action = $this->input->post('action');
            $selected_ids = $this->input->post('selected_ids') ?? [];

            if ($action === 'ignore') {
                $deleted = $this->Ad_model->delete_ads($selected_ids);
                if ($deleted > 0) {
                    $this->session->set_flashdata('success', "{$deleted} unconnected ads successfully ignored and removed.");
                } else {
                    $this->session->set_flashdata('errors', 'Failed to ignore selected ads.');
                }
            } else {
                // default is connect
                $campaign_map = $this->input->post('campaign_id') ?? [];
                $saved = 0;
                foreach ($selected_ids as $ad_id) {
                    $ad_id = (int) $ad_id;
                    $campaign_id = (int) ($campaign_map[$ad_id] ?? 0);
                    if ($campaign_id > 0) {
                        $rows = $this->Ad_model->assign_campaign($ad_id, $campaign_id);
                        $saved += $rows;
                    }
                }

                if ($saved > 0) {
                    $this->session->set_flashdata('success', "{$saved} ads successfully connected to campaign.");
                } else {
                    $this->session->set_flashdata('errors', 'No ads were successfully saved. Please ensure a campaign is selected for the checked rows.');
                }
            }

            redirect('ads');
            return;
        }

        $platform_labels = [];
        foreach ($this->platform_registry->configs() as $name => $conf) {
            $platform_labels[$name] = $conf['label'] ?? ucfirst($name);
        }

        $filters = [
            'q' => $this->input->get('q'),
            'platform' => $this->input->get('platform')
        ];

        $data = [
            'title' => 'Connect Ads',
            'active_menu' => 'ads',
            'filters' => $filters,
            'campaigns' => $this->Campaign_model->get_all(),
            'unconnected' => $this->Ad_model->get_unconnected_ads($filters),
            'platform_labels' => $platform_labels
        ];

        $this->render('ads/index', $data);
    }
}
