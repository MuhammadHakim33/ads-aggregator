<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ad_model extends CI_Model
{
    public function bulk_upsert_contents($rows)
    {
        if (empty($rows)) {
            return ['created' => 0];
        }

        $placeholders = [];

        foreach ($rows as $row) {
            $title = isset($row['title']) ? $this->db->escape($row['title']) : 'NULL';
            $platform = $this->db->escape($row['platform']);
            $content_identifier = $this->db->escape($row['content_identifier']);
            $published_at = isset($row['published_at']) ? $this->db->escape($row['published_at']) : 'NULL';

            $placeholders[] = "({$title}, {$platform}, {$content_identifier}, {$published_at})";
        }

        $sql = "INSERT IGNORE INTO " . 'ad_contents' . " (title, platform, content_identifier, published_at) VALUES " . implode(',', $placeholders);

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
            $ad_content_id = (int) $row['ad_content_id'];
            $metric_name = $this->db->escape($row['metric_name']);
            $metric_value = $this->db->escape($row['metric_value']);

            $placeholders[] = "({$ad_content_id}, {$metric_name}, {$metric_value})";
        }

        $values = implode(', ', $placeholders);

        $sql = "INSERT INTO ad_metrics (ad_content_id, metric_name, metric_value)
                VALUES {$values}
                ON DUPLICATE KEY UPDATE
                    metric_value = VALUES(metric_value),
                    updated_at   = CURRENT_TIMESTAMP";

        $this->db->query($sql);

        return ['upserted' => $this->db->affected_rows()];
    }

    public function get_identifiers_by_platform($platform)
    {
        $platform = $this->db->escape_str($platform);

        $sql = "
            SELECT ac.id, ac.content_identifier,
                   camp.start_date AS campaign_start_date,
                   camp.end_date   AS campaign_end_date
            FROM ad_contents ac
            LEFT JOIN campaigns camp ON camp.id = ac.campaign_id
            WHERE ac.platform = '{$platform}'
              AND (
                ac.campaign_id IS NULL
                OR (camp.end_date >= CURDATE() AND camp.deleted_at IS NULL)
              )
        ";

        return $this->db->query($sql)->result();
    }

    public function count_unconnected()
    {
        $this->db->where('campaign_id IS NULL', NULL, FALSE);
        return $this->db->count_all_results('ad_contents');
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
                a.content_identifier,
                a.source,
                a.created_at,
                camp.id        AS campaign_id,
                camp.name      AS campaign_name,
                c.id           AS client_id,
                c.company_name,
                (SELECT name FROM client_pics WHERE client_id = c.id AND is_active = 1 LIMIT 1) AS pic_name
            FROM ad_contents a
            LEFT JOIN campaigns camp ON camp.id = a.campaign_id
            LEFT JOIN contracts cont ON cont.id = camp.contract_id
            LEFT JOIN clients c ON c.id = cont.client_id
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
        $metrics = $this->db->get('ad_metrics')->result();

        $ad->metrics = $metrics;

        return $ad;
    }

    public function count_all_ads_by_client($client_id = null)
    {
        $this->db->join('campaigns', 'campaigns.id = ad_contents.campaign_id', 'inner');
        $this->db->join('contracts', 'contracts.id = campaigns.contract_id', 'inner');

        $this->db->where('campaigns.deleted_at', NULL);
        $this->db->where('campaigns.end_date >=', date('Y-m-d'));

        $this->db->where('contracts.deleted_at', NULL);
        $this->db->where('contracts.terminated_at', NULL);
        $this->db->where('contracts.status', 'approved');

        if ($client_id) {
            $this->db->where('contracts.client_id', $client_id);
        }

        return $this->db->count_all_results('ad_contents');
    }

    public function get_unconnected_ads($filters = [])
    {
        $this->db->where('campaign_id IS NULL', NULL, FALSE);

        if (!empty($filters['q'])) {
            $q = $this->db->escape_like_str($filters['q']);
            $this->db->group_start();
            $this->db->like('title', $q);
            $this->db->or_like('content_identifier', $q);
            $this->db->group_end();
        }

        if (!empty($filters['platform'])) {
            $this->db->where('platform', $filters['platform']);
        }

        $this->db->order_by('platform', 'ASC');
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get('ad_contents')->result();
    }

    public function assign_campaign($ad_content_id, $campaign_id)
    {
        $this->db->where('id', $ad_content_id);
        $this->db->update('ad_contents', ['campaign_id' => $campaign_id ?: null]);
        return $this->db->affected_rows();
    }

    public function delete_ads($ids)
    {
        if (empty($ids))
            return 0;

        // Delete related metrics first
        $this->db->where_in('ad_content_id', $ids);
        $this->db->delete('ad_metrics');

        // Delete ads contents
        $this->db->where_in('id', $ids);
        $this->db->delete('ad_contents');

        return $this->db->affected_rows();
    }

    public function get_ads_by_client($client_id)
    {
        $this->db->select('ad_contents.id, ad_contents.title, ad_contents.platform, campaigns.name as campaign_name');
        $this->db->from('ad_contents');
        $this->db->join('campaigns', 'campaigns.id = ad_contents.campaign_id', 'inner');
        $this->db->join('contracts', 'contracts.id = campaigns.contract_id', 'inner');
        $this->db->where('contracts.client_id', $client_id);
        $this->db->order_by('campaigns.name', 'ASC');
        $this->db->order_by('ad_contents.title', 'ASC');
        return $this->db->get()->result();
    }

    public function update_source($ad_content_id, $source)
    {
        $this->db->where('id', $ad_content_id);
        $this->db->update('ad_contents', ['source' => $source]);
        return $this->db->affected_rows();
    }
}