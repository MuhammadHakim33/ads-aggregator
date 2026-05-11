<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ad_content_model extends CI_Model 
{
    private $_table_contents = 'ad_contents';
    private $_table_metrics = 'ad_metrics';

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

        $sql = "INSERT IGNORE INTO ". $this->_table_contents ." (client_id, title, platform, content_identifier, ad_type) VALUES " . implode(',', $placeholders);

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

        $table = $this->_table_metrics;
        $values = implode(', ', $placeholders);

        $sql = "INSERT INTO {$table} (ad_content_id, metric_name, metric_value)
                VALUES {$values}
                ON DUPLICATE KEY UPDATE
                    metric_value = VALUES(metric_value),
                    updated_at   = CURRENT_TIMESTAMP";

        $this->db->query($sql);

        return ['upserted' => $this->db->affected_rows()];
    }

    public function get_identifiers_by_platform($platform, $since, $until)
    {
        $this->db->select('id, content_identifier');
        $this->db->where('is_active', 1);
        $this->db->where('platform', $platform);
        
        if ($since && $until) {
            $this->db->where(" DATE(created_at) BETWEEN '{$since}' AND '{$until}' ");
        }

        return $this->db->get($this->_table_contents)->result();
    }

    public function count_active()
    {
        $this->db->where('is_active', 1);
        return $this->db->count_all_results($this->_table_contents);
    }
}