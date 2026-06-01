<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ad_metrics extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Ad_model');
        $this->load->model('Client_model');
        $this->load->library('Export_registry');
    }

    public function index()
    {
        $data = [
            'title' => 'Ad Metrics',
            'active_menu' => 'ad_metrics',
            'clients' => $this->Client_model->get_clients_summary()
        ];

        $this->render('ad_metrics/index', $data);
    }

    public function detail($id)
    {
        // check client exist
        $client = $this->Client_model->get_by_id($id);
        if (!$client) {
            $this->session->set_flashdata('errors', 'Client not found.');
            redirect('ad-metrics');
            return;
        }

        // get ad contents and metrics
        $ad_contents = $this->Ad_model->get_client_ad_metrics($id);

        $data = [
            'title' => 'Detail Metrik - ' . $client->company_name,
            'active_menu' => 'ad_metrics',
            'ad_contents' => $ad_contents,
            'client' => $client
        ];

        $this->render('ad_metrics/detail', $data);
    }

    public function mapping()
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
                $this->session->set_flashdata('success', "{$saved} ads successfully mapped to client.");
            } else {
                $this->session->set_flashdata('errors', 'No ads was successfully saved. Please select a client for the checked rows.');
            }

            redirect('ad-metrics/mapping');
            return;
        }

        $data = [
            'title' => 'Ad Content Mapping',
            'active_menu' => 'ad_metrics',
            'clients' => $this->Client_model->get_all(),
            'unmapped' => $this->Ad_model->get_unmapped_contents()
        ];

        $this->render('ad_metrics/mapping', $data);
    }

    public function export($format, $id)
    {
        $ad = $this->Ad_model->get_ad_with_metrics($id);
        if (!$ad) {
            show_error('Ad content not found.', 404);
            return;
        }

        // check if format is supported
        if (!$this->export_registry->has($format)) {
            show_error("Unknown export format: {$format}", 400);
            return;
        }

        // create exporter
        $exporter = $this->make_exporter($format);
        $filename = 'report_' . $id . '_' . date('Ymd');
        $exporter->generate($ad, $filename);
    }

    private function make_exporter($format)
    {
        // prepare conf
        $conf = $this->export_registry->configs()[$format];
        // include file
        require_once $conf['class_path'];
        // instantiate
        $class = $conf['class'];
        return new $class();
    }

    // public function export_pdf($id)
    // {
    //     // get ad
    //     $ad = $this->Ad_model->get_ad_with_metrics($id);
    //     if (!$ad) {
    //         show_error('Ad content not found.', 404);
    //         return;
    //     }

    //     // render the html template to a string
    //     $html = $this->load->view('templates/pdf', ['ad' => $ad], TRUE);

    //     // load library and stream pdf
    //     $this->load->library('Export_pdf');
    //     $filename = 'ad_report_' . $id . '_' . date('Ymd');
    //     $this->export_pdf->generate($html, $filename);
    // }

    // public function export_excel($id)
    // {
    //     // get ad
    //     $ad = $this->Ad_model->get_ad_with_metrics($id);
    //     if (!$ad) {
    //         show_error('Ad content not found.', 404);
    //         return;
    //     }

    //     // load library and stream excel file
    //     $this->load->library('Export_excel');
    //     $filename = 'ad_report_' . $id . '_' . date('Ymd');
    //     $this->export_excel->generate($ad, $filename);
    // }
}

