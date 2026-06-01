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
            'title' => 'All Ads',
            'active_menu' => 'ads',
            'ad_contents' => $this->Ad_model->get_all_ad_metrics()
        ];

        $this->render('ads/index', $data);
    }
}
