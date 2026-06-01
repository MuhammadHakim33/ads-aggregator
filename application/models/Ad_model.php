<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ad_model extends CI_Model 
{
    private $table_contents = 'ad_contents';
    private $table_metrics = 'ad_metrics';

    public function bulk_upsert_contents($rows)
    {
        if (empty($rows)) {
            return ['created' => 0];
        }

        $placeholders = [];

        foreach ($rows as $row) {
            $client_id = isset($row['client_id']) ? (int)$row['client_id'] : 'NULL';
            $title = isset($row['title']) ? $this->db->escape($row['title']) : 'NULL';
            $platform = $this->db->escape($row['platform']);
            $content_identifier = $this->db->escape($row['content_identifier']);
            $ad_type = $this->db->escape($row['ad_type']);

            $placeholders[] = "({$client_id}, {$title}, {$platform}, {$content_identifier}, {$ad_type})";
        }

        $sql = "INSERT IGNORE INTO ". $this->table_contents ." (client_id, title, platform, content_identifier, ad_type) VALUES " . implode(',', $placeholders);

        $this->db->query($sql);

        return ['created' => $this->db->affected_rows()];
    }

    public function bulk_upsert_metrics($rows)
    {
        if (empty($rows)) {
            return ['upserted' => 0];
        }

        $placeholders = [];

        foreach ($rows as $row) {
            $ad_content_id  = (int)$row['ad_content_id'];
            $metric_name    = $this->db->escape($row['metric_name']);
            $metric_value   = $this->db->escape($row['metric_value']);

            $placeholders[] = "({$ad_content_id}, {$metric_name}, {$metric_value})";
        }

        $values = implode(', ', $placeholders);

        $sql = "INSERT INTO {$this->table_metrics} (ad_content_id, metric_name, metric_value)
                VALUES {$values}
                ON DUPLICATE KEY UPDATE
                    metric_value = VALUES(metric_value),
                    updated_at   = CURRENT_TIMESTAMP";

        $this->db->query($sql);

        return ['upserted' => $this->db->affected_rows()];
    }

    public function get_client_ad_metrics($client_id)
    {
        // get ad contents for the client
        $this->db->where('client_id', $client_id);
        $ad_contents = $this->db->get($this->table_contents)->result();

        if (empty($ad_contents)) {
            return [];
        }

        // get metrics for these ads
        $ad_ids = array_column($ad_contents, 'id');
        
        $this->db->where_in('ad_content_id', $ad_ids);
        $this->db->order_by('metric_name', 'ASC');
        $metrics = $this->db->get($this->table_metrics)->result();
        
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

    public function get_identifiers_by_platform($platform, $since, $until)
    {
        $this->db->select('id, content_identifier');
        $this->db->where('is_active', 1);
        $this->db->where('platform', $platform);
        
        if ($since && $until) {
            $this->db->where(" DATE(created_at) BETWEEN '{$since}' AND '{$until}' ");
        }

        return $this->db->get($this->table_contents)->result();
    }

    public function count_active()
    {
        $this->db->where('is_active', 1);
        return $this->db->count_all_results($this->table_contents);
    }

    public function get_unmapped_contents()
    {
        $this->db->where('client_id IS NULL', NULL, FALSE);
        $this->db->order_by('platform', 'ASC');
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get($this->table_contents)->result();
    }

    public function get_ad_with_metrics($ad_content_id)
    {
        $ad_content_id = (int) $ad_content_id;

        // get the ad content joined with client info
        $query = $this->db->query("
            SELECT
                a.id,
                a.title,
                a.platform,
                a.ad_type,
                a.content_identifier,
                a.is_active,
                a.created_at,
                c.id          AS client_id,
                c.company_name,
                c.pic_name
            FROM {$this->table_contents} a
            LEFT JOIN clients c ON c.id = a.client_id
            WHERE a.id = {$ad_content_id}
            LIMIT 1
        ");

        $ad = $query->row();

        if (!$ad) {
            return null;
        }

        // get all metrics for this ad
        $this->db->where('ad_content_id', $ad_content_id);
        $this->db->order_by('metric_name', 'ASC');
        $metrics = $this->db->get($this->table_metrics)->result();

        $ad->metrics = $metrics;

        return $ad;
    }

    public function count_all_ads()
    {
        return $this->db->count_all_results($this->table_contents);
    }

    public function get_all_ad_metrics($limit = null, $offset = null)
    {
        $this->db->select('a.*, c.company_name');
        $this->db->from($this->table_contents . ' a');
        $this->db->join('clients c', 'c.id = a.client_id', 'left');
        $this->db->order_by('a.created_at', 'DESC');
        
        if ($limit !== null) {
            if ($offset !== null) {
                $this->db->limit($limit, $offset);
            } else {
                $this->db->limit($limit);
            }
        }

        $ad_contents = $this->db->get()->result();

        if (empty($ad_contents)) {
            return [];
        }

        $ad_ids = array_column($ad_contents, 'id');
        
        // Chunk metrics query just in case the limit is high
        $metrics = [];
        $chunks = array_chunk($ad_ids, 500);
        foreach ($chunks as $chunk) {
            $this->db->where_in('ad_content_id', $chunk);
            $this->db->order_by('metric_name', 'ASC');
            $res = $this->db->get($this->table_metrics)->result();
            $metrics = array_merge($metrics, $res);
        }
        
        $metrics_by_ad = [];
        foreach ($metrics as $m) {
            $metrics_by_ad[$m->ad_content_id][] = $m;
        }
        
        foreach ($ad_contents as &$ad) {
            $ad->metrics = isset($metrics_by_ad[$ad->id]) ? $metrics_by_ad[$ad->id] : [];
        }

        return $ad_contents;
    }
}