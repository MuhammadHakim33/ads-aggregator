<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Campaign_model extends CI_Model
{
    public function get_all($filters = [])
    {
        $this->db->select('campaigns.*, contracts.contract_number, clients.company_name as client_name');
        $this->db->from('campaigns');
        $this->db->join('contracts', 'contracts.id = campaigns.contract_id', 'inner');
        $this->db->join('clients', 'clients.id = contracts.client_id', 'inner');
        $this->db->where('campaigns.deleted_at', NULL);

        if (!empty($filters['q'])) {
            $q = $this->db->escape_like_str($filters['q']);
            $this->db->group_start();
            $this->db->like('campaigns.name', $q);
            $this->db->or_like('campaigns.description', $q);
            $this->db->or_like('clients.company_name', $q);
            $this->db->group_end();
        }

        if (!empty($filters['client_id'])) {
            $this->db->where('contracts.client_id', $filters['client_id']);
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            if ($filters['status'] == '1') {
                $this->db->where('campaigns.start_date <=', date('Y-m-d'));
                $this->db->where('campaigns.end_date >=', date('Y-m-d'));
            } else {
                $this->db->group_start();
                $this->db->where('campaigns.start_date >', date('Y-m-d'));
                $this->db->or_where('campaigns.end_date <', date('Y-m-d'));
                $this->db->group_end();
            }
        }

        if (!empty($filters['ae_id'])) {
            $this->db->where('clients.ae_id', $filters['ae_id']);
        }

        $this->db->order_by('campaigns.created_at', 'DESC');
        return $this->db->get()->result();
    }

    public function get_by_id($id)
    {
        $this->db->select('campaigns.*, contracts.contract_number, clients.company_name as client_name, contracts.start_date as contract_start, contracts.end_date as contract_end, contracts.terminated_at as contract_terminated, contracts.client_id, clients.ae_id');
        $this->db->from('campaigns');
        $this->db->join('contracts', 'contracts.id = campaigns.contract_id', 'inner');
        $this->db->join('clients', 'clients.id = contracts.client_id', 'inner');
        $this->db->where('campaigns.id', $id);
        $this->db->where('campaigns.deleted_at', NULL);
        return $this->db->get()->row();
    }

    public function insert($data)
    {
        $this->db->insert('campaigns', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->where('deleted_at', NULL);
        $this->db->update('campaigns', $data);
        return $this->db->affected_rows();
    }

    public function delete($id)
    {
        $data = [
            'deleted_at' => date('Y-m-d H:i:s')
        ];
        $this->db->where('id', $id);
        $this->db->update('campaigns', $data);
        return $this->db->affected_rows();
    }

    public function get_by_contract_id($contract_id)
    {
        $this->db->where('contract_id', $contract_id);
        $this->db->where('deleted_at', NULL);
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get('campaigns')->result();
    }

    public function has_ads($id)
    {
        $this->db->where('campaign_id', $id);
        return $this->db->count_all_results('ad_contents') > 0;
    }

    public function count_running($client_id = null, $ae_id = null)
    {
        $this->db->where('campaigns.deleted_at', NULL);
        $this->db->where('campaigns.start_date <=', date('Y-m-d'));
        $this->db->where('campaigns.end_date >=', date('Y-m-d'));
        if ($client_id || $ae_id) {
            $this->db->join('contracts', 'contracts.id = campaigns.contract_id', 'inner');
            if ($client_id) {
                $this->db->where('contracts.client_id', $client_id);
            }
            if ($ae_id) {
                $this->db->join('clients', 'clients.id = contracts.client_id', 'inner');
                $this->db->where('clients.ae_id', $ae_id);
            }
        }
        return $this->db->count_all_results('campaigns');
    }

    public function get_campaign_with_ads_and_metrics($campaign_id)
    {
        // get campaign details with contract and client info
        $this->db->select('camp.*, cont.contract_number, cont.value as contract_value, c.company_name as client_name, cont.client_id, c.ae_id');
        $this->db->from('campaigns' . ' camp');
        $this->db->join('contracts cont', 'cont.id = camp.contract_id', 'left');
        $this->db->join('clients c', 'c.id = cont.client_id', 'left');
        $this->db->where('camp.id', $campaign_id);
        $this->db->where('camp.deleted_at', NULL);
        $campaign = $this->db->get()->row();

        if (!$campaign) {
            return null;
        }

        // initialize reported metrics flag
        $campaign->has_reported_metrics = false;

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

            // create map of ad ID to its platform
            $ad_platforms = [];
            foreach ($ads as $ad) {
                $ad_platforms[$ad->id] = $ad->platform;
            }

            // Fetch configured metrics if any
            $this->db->where('campaign_id', $campaign_id);
            $reported_metrics = $this->db->get('campaign_reported_metrics')->result();

            $has_configured_metrics = !empty($reported_metrics);
            $campaign->has_reported_metrics = $has_configured_metrics;
            $reported_map = [];
            if ($has_configured_metrics) {
                foreach ($reported_metrics as $rm) {
                    $reported_map[$rm->platform][$rm->metric_name] = true;
                }
            }

            // map metrics to ads
            $metrics_by_ad = [];
            foreach ($metrics as $m) {
                $ad_platform = $ad_platforms[$m->ad_content_id] ?? null;
                if ($has_configured_metrics && $ad_platform) {
                    if (!isset($reported_map[$ad_platform][$m->metric_name])) {
                        continue;
                    }
                }
                $metrics_by_ad[$m->ad_content_id][] = $m;
            }

            foreach ($ads as &$ad) {
                $ad->metrics = $metrics_by_ad[$ad->id] ?? [];
            }
        }

        $campaign->ads = $ads;
        return $campaign;
    }

    public function get_reported_metrics($campaign_id)
    {
        $this->db->where('campaign_id', $campaign_id);
        return $this->db->get('campaign_reported_metrics')->result();
    }

    public function save_reported_metrics($campaign_id, $selected_metrics)
    {
        $this->db->trans_start();

        // delete existing
        $this->db->where('campaign_id', $campaign_id);
        $this->db->delete('campaign_reported_metrics');

        // insert new
        if (!empty($selected_metrics)) {
            $insert_data = [];
            foreach ($selected_metrics as $platform => $metrics) {
                if (is_array($metrics)) {
                    foreach ($metrics as $metric_name) {
                        $insert_data[] = [
                            'campaign_id' => $campaign_id,
                            'platform' => $platform,
                            'metric_name' => $metric_name
                        ];
                    }
                }
            }

            if (!empty($insert_data)) {
                $this->db->insert_batch('campaign_reported_metrics', $insert_data);
            }
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function deactivate_by_contract($contract_id)
    {
        $this->db->where('contract_id', $contract_id);
        $this->db->where('end_date >=', date('Y-m-d'));
        $this->db->update('campaigns', ['end_date' => date('Y-m-d')]);
        return $this->db->affected_rows();
    }
}
