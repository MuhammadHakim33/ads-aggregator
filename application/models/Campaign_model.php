<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Campaign_model extends CI_Model
{
    public $table = 'campaigns';

    public function __construct()
    {
        parent::__construct();

        // Safety check: Create the campaigns table if it doesn't exist
        if (!$this->db->table_exists($this->table)) {
            $sql = "CREATE TABLE IF NOT EXISTS campaigns (
              id INT PRIMARY KEY AUTO_INCREMENT,
              contract_id INT NOT NULL,
              name VARCHAR(255) NOT NULL,
              description TEXT NULL,
              start_date DATE NOT NULL,
              end_date DATE NOT NULL,
              is_active BOOLEAN DEFAULT TRUE,
              deleted_at TIMESTAMP NULL,
              created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              
              FOREIGN KEY (contract_id) REFERENCES contracts(id)
            )";
            $this->db->query($sql);
        } else {
            // Dynamically verify and add deleted_at if missing (safety check for option A)
            if (!$this->db->field_exists('deleted_at', $this->table)) {
                $this->load->dbforge();
                $fields = [
                    'deleted_at' => [
                        'type' => 'TIMESTAMP',
                        'null' => TRUE,
                        'default' => NULL
                    ]
                ];
                $this->dbforge->add_column($this->table, $fields);
            }
        }
    }

    public function get_all()
    {
        $this->db->select('campaigns.*, contracts.contract_number, clients.company_name as client_name');
        $this->db->from($this->table);
        $this->db->join('contracts', 'contracts.id = campaigns.contract_id', 'inner');
        $this->db->join('clients', 'clients.id = contracts.client_id', 'inner');
        $this->db->where('campaigns.deleted_at', NULL);
        $this->db->order_by('campaigns.created_at', 'DESC');
        return $this->db->get()->result();
    }

    public function get_by_id($id)
    {
        $this->db->select('campaigns.*, contracts.contract_number, clients.company_name as client_name, contracts.start_date as contract_start, contracts.end_date as contract_end, contracts.terminated_at as contract_terminated');
        $this->db->from($this->table);
        $this->db->join('contracts', 'contracts.id = campaigns.contract_id', 'inner');
        $this->db->join('clients', 'clients.id = contracts.client_id', 'inner');
        $this->db->where('campaigns.id', $id);
        $this->db->where('campaigns.deleted_at', NULL);
        return $this->db->get()->row();
    }

    public function insert($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->where('deleted_at', NULL);
        $this->db->update($this->table, $data);
        return $this->db->affected_rows();
    }

    public function delete($id)
    {
        $data = [
            'deleted_at' => date('Y-m-d H:i:s')
        ];
        $this->db->where('id', $id);
        $this->db->update($this->table, $data);
        return $this->db->affected_rows();
    }

    public function has_ads($id)
    {
        $this->db->where('campaign_id', $id);
        $this->db->where('is_active', 1);
        return $this->db->count_all_results('ad_contents') > 0;
    }

    public function get_campaign_with_ads_and_metrics($campaign_id)
    {
        // get campaign details with contract and client info
        $this->db->select('camp.*, cont.contract_number, cont.value as contract_value, c.company_name as client_name, c.pic_name as client_pic');
        $this->db->from($this->table . ' camp');
        $this->db->join('contracts cont', 'cont.id = camp.contract_id', 'left');
        $this->db->join('clients c', 'c.id = cont.client_id', 'left');
        $this->db->where('camp.id', $campaign_id);
        $this->db->where('camp.deleted_at', NULL);
        $campaign = $this->db->get()->row();

        if (!$campaign) {
            return null;
        }

        // get all ads associated with this campaign
        $this->db->select('ad.*');
        $this->db->from('ad_contents ad');
        $this->db->where('ad.campaign_id', $campaign_id);
        $ads = $this->db->get()->result();

        if (!empty($ads)) {
            $ad_ids = array_column($ads, 'id');

            // get all metrics for these ads
            $this->db->where_in('ad_content_id', $ad_ids);
            $this->db->order_by('metric_name', 'ASC');
            $metrics = $this->db->get('ad_metrics')->result();

            // map metrics to ads
            $metrics_by_ad = [];
            foreach ($metrics as $m) {
                $metrics_by_ad[$m->ad_content_id][] = $m;
            }

            foreach ($ads as &$ad) {
                $ad->metrics = $metrics_by_ad[$ad->id] ?? [];
            }
        }

        $campaign->ads = $ads;
        return $campaign;
    }
}
