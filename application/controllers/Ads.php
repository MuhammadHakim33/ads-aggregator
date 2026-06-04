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
        $platform_labels = [];
        foreach ($this->platform_registry->configs() as $name => $conf) {
            $platform_labels[$name] = $conf['label'] ?? ucfirst($name);
        }

        $data = [
            'title' => 'Ads',
            'active_menu' => 'ads',
            'ad_contents' => $this->Ad_model->get_all_ad_metrics(),
            'platform_labels' => $platform_labels
        ];

        $this->render('ads/index', $data);
    }

    public function connect()
    {
        if ($this->input->method() === 'post') {
            $selected_ids = $this->input->post('selected_ids') ?? [];
            $client_map = $this->input->post('client_id') ?? [];

            $saved = 0;
            foreach ($selected_ids as $ad_id) {
                $ad_id = (int) $ad_id;
                $client_id = (int) $client_map[$ad_id];

                $rows = $this->Client_model->assign_client($ad_id, $client_id);
                $saved += $rows;
            }

            if ($saved > 0) {
                $this->session->set_flashdata('success', "{$saved} ads successfully connected to client.");
            } else {
                $this->session->set_flashdata('errors', 'No ads were successfully saved. Please select a client for the checked rows.');
            }

            redirect('ads/connect');
            return;
        }

        $platform_labels = [];
        foreach ($this->platform_registry->configs() as $name => $conf) {
            $platform_labels[$name] = $conf['label'] ?? ucfirst($name);
        }

        $data = [
            'title' => 'Connect Ads',
            'active_menu' => 'ads',
            'clients' => $this->Client_model->get_all(),
            'unconnected' => $this->Ad_model->get_unconnected_ads(),
            'platform_labels' => $platform_labels
        ];

        $this->render('ads/connect', $data);
    }

    public function export($format, $id)
    {
        $ad = $this->Ad_model->get_ad_with_metrics($id);
        if (!$ad) {
            show_error('Ad content not found.', 404);
            return;
        }

        if (!$this->export_registry->has($format)) {
            show_error("Unknown export format: {$format}", 400);
            return;
        }

        $exporter = $this->make_exporter($format);
        $filename = 'report_' . $id . '_' . date('Ymd');
        $exporter->generate($ad, $filename);
    }

    private function make_exporter($format)
    {
        $conf = $this->export_registry->configs()[$format];
        require_once $conf['class_path'];
        $class = $conf['class'];
        return new $class();
    }
}
