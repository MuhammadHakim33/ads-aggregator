<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ads extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Ad_model');
    }

    public function index()
    {
        $data = [
            'title' => 'Ads',
            'active_menu' => 'ads',
            'ad_contents' => $this->Ad_model->get_all_ad_metrics()
        ];

        $this->render('ads/index', $data);
    }

    public function export($format, $id)
    {
        $this->load->library('Export_registry');

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
