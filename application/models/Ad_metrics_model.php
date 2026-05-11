<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ad_metrics_model extends CI_Model
{
    public function get_clients_summary()
    {
        $query = $this->db->query("
            SELECT 
                c.id, 
                c.company_name, 
                c.pic_name,
                COUNT(a.id) as total_ads,
                SUM(CASE WHEN a.is_active = 1 THEN 1 ELSE 0 END) as active_ads,
                GROUP_CONCAT(DISTINCT a.platform SEPARATOR ',') as platforms
            FROM clients c
            LEFT JOIN ad_contents a ON c.id = a.client_id
            WHERE c.deleted_at IS NULL
            GROUP BY c.id
            ORDER BY c.company_name ASC
        ");

        return $query->result();
    }

    public function get_client_ad_metrics($client_id)
    {
        // get ad contents for the client
        $this->db->where('client_id', $client_id);
        $ad_contents = $this->db->get('ad_contents')->result();

        if (empty($ad_contents)) {
            return [];
        }

        // get metrics for these ads
        $ad_ids = array_column($ad_contents, 'id');
        
        $this->db->where_in('ad_content_id', $ad_ids);
        $this->db->order_by('metric_name', 'ASC');
        $metrics = $this->db->get('ad_metrics')->result();
        
        // group metrics by ad_content_id
        $metrics_by_ad = [];
        foreach ($metrics as $m) {
            $metrics_by_ad[$m->ad_content_id][] = $m;
        }
        
        // assign metrics back to ad_contents
        foreach ($ad_contents as &$ad) {
            $ad->metrics = isset($metrics_by_ad[$ad->id]) ? $metrics_by_ad[$ad->id] : [];
        }

        return $ad_contents;
    }

    public function get_unmapped_contents()
    {
        $this->db->where('client_id IS NULL', NULL, FALSE);
        $this->db->order_by('platform', 'ASC');
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get('ad_contents')->result();
    }

    public function assign_client($ad_content_id, $client_id)
    {
        $this->db->where('id', $ad_content_id);
        $this->db->where('client_id IS NULL', NULL, FALSE);
        $this->db->update('ad_contents', ['client_id' => (int)$client_id]);
        return $this->db->affected_rows();
    }
}
