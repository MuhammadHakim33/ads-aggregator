<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ad_metrics extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        
        // Require login
        if (!$this->session->userdata('logged_in')) {
            redirect('auth');
        }
    }

    public function index()
    {
        // DUMMY DATA: Clients with aggregated metrics
        $dummy_clients = [
            (object) [
                'id' => 1,
                'company_name' => 'PT Maju Bersama',
                'pic_name' => 'Andi Susanto',
                'total_ads' => 15,
                'active_ads' => 10,
                'platforms' => 'facebook,instagram,youtube'
            ],
            (object) [
                'id' => 2,
                'company_name' => 'CV Sukses Makmur',
                'pic_name' => 'Budi Hartono',
                'total_ads' => 5,
                'active_ads' => 5,
                'platforms' => 'gam'
            ],
            (object) [
                'id' => 3,
                'company_name' => 'Tech Solutions Corp',
                'pic_name' => 'Citra Lestari',
                'total_ads' => 24,
                'active_ads' => 0,
                'platforms' => 'facebook,ga4'
            ]
        ];

        $data = [
            'title' => 'Ad Metrics',
            'clients' => $dummy_clients,
            'active_menu' => 'ad_metrics',
            'current_account' => [
                'name' => $this->session->userdata('name'),
                'role' => $this->session->userdata('role')
            ]
        ];

        $this->load->view('ad_metrics/index', $data);
    }

    public function detail($client_id)
    {
        $client_id = (int) $client_id;
        
        // DUMMY DATA: Client Info
        $dummy_client = (object) [
            'id' => $client_id,
            'company_name' => $client_id === 2 ? 'CV Sukses Makmur' : ($client_id === 3 ? 'Tech Solutions Corp' : 'PT Maju Bersama'),
            'pic_name' => $client_id === 2 ? 'Budi Hartono' : ($client_id === 3 ? 'Citra Lestari' : 'Andi Susanto'),
        ];

        // DUMMY DATA: Ad Contents and their Metrics
        $dummy_ad_contents = [];
        
        if ($client_id === 1 || $client_id === 2 || $client_id === 3) {
            $dummy_ad_contents = [
                (object) [
                    'id' => 101,
                    'client_id' => $client_id,
                    'title' => 'Kampanye Promo Lebaran - FB',
                    'platform' => 'facebook',
                    'content_identifier' => 'promo_lebaran_fb_01',
                    'ad_type' => 'social',
                    'is_active' => 1,
                    'metrics' => [
                        (object) ['metric_name' => 'impressions', 'metric_value' => 150500, 'updated_at' => date('Y-m-d H:i:s')],
                        (object) ['metric_name' => 'clicks', 'metric_value' => 3200, 'updated_at' => date('Y-m-d H:i:s')],
                        (object) ['metric_name' => 'spend', 'metric_value' => 1500000, 'updated_at' => date('Y-m-d H:i:s')]
                    ]
                ],
                (object) [
                    'id' => 102,
                    'client_id' => $client_id,
                    'title' => 'Video Teaser Product Baru',
                    'platform' => 'youtube',
                    'content_identifier' => 'yt_teaser_02',
                    'ad_type' => 'video',
                    'is_active' => 1,
                    'metrics' => [
                        (object) ['metric_name' => 'views', 'metric_value' => 85000, 'updated_at' => date('Y-m-d H:i:s')],
                        (object) ['metric_name' => 'watch_time_hours', 'metric_value' => 450.5, 'updated_at' => date('Y-m-d H:i:s')]
                    ]
                ],
                (object) [
                    'id' => 103,
                    'client_id' => $client_id,
                    'title' => 'Banner Header Website',
                    'platform' => 'gam',
                    'content_identifier' => 'gam_header_03',
                    'ad_type' => 'banner',
                    'is_active' => 0,
                    'metrics' => [
                        (object) ['metric_name' => 'impressions', 'metric_value' => 500000, 'updated_at' => date('Y-m-d H:i:s', strtotime('-1 days'))],
                        (object) ['metric_name' => 'clicks', 'metric_value' => 1500, 'updated_at' => date('Y-m-d H:i:s', strtotime('-1 days'))]
                    ]
                ],
                (object) [
                    'id' => 104,
                    'client_id' => $client_id,
                    'title' => 'Artikel Advertorial Q1',
                    'platform' => 'ga4',
                    'content_identifier' => '/artikel/advertorial-q1',
                    'ad_type' => 'article',
                    'is_active' => 1,
                    'metrics' => [
                        (object) ['metric_name' => 'page_views', 'metric_value' => 12500, 'updated_at' => date('Y-m-d H:i:s')],
                        (object) ['metric_name' => 'avg_engagement_time', 'metric_value' => 120, 'updated_at' => date('Y-m-d H:i:s')]
                    ]
                ],
                (object) [
                    'id' => 105,
                    'client_id' => $client_id,
                    'title' => 'IG Story Sale',
                    'platform' => 'instagram',
                    'content_identifier' => 'ig_story_sale_05',
                    'ad_type' => 'social',
                    'is_active' => 1,
                    'metrics' => [] // Example of ad without metrics
                ]
            ];
        }

        $data = [
            'title' => 'Metrics - ' . $dummy_client->company_name,
            'client' => $dummy_client,
            'ad_contents' => $dummy_ad_contents,
            'active_menu' => 'ad_metrics',
            'current_account' => [
                'name' => $this->session->userdata('name'),
                'role' => $this->session->userdata('role')
            ]
        ];

        $this->load->view('ad_metrics/detail', $data);
    }
}
