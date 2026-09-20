<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Cron_log_model extends CI_Model
{
    public function start($job_name, $platform)
    {
        $this->db->insert('cron_logs', [
            'job_name' => $job_name,
            'platform' => $platform,
            'status' => 'failed',
            'started_at' => date('Y-m-d H:i:s'),
            'finished_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->db->insert_id();
    }

    public function finish($id, $status, $rows_affected = 0, $error_message = null)
    {
        $duration = $this->_elapsed_ms($id);

        $this->db->where('id', $id);
        $this->db->update('cron_logs', [
            'status' => $status,
            'rows_affected' => $rows_affected,
            'error_message' => $error_message,
            'finished_at' => date('Y-m-d H:i:s'),
            'duration_ms' => $duration,
        ]);
    }

    public function get_latest($limit = 50)
    {
        $this->db->order_by('id', 'DESC');
        $this->db->limit($limit);
        return $this->db->get('cron_logs')->result();
    }


    public function get_last_per_platform()
    {
        $sql = "
            SELECT cl.*
            FROM cron_logs cl
            INNER JOIN (
                SELECT platform, job_name, MAX(id) as max_id
                FROM cron_logs
                GROUP BY platform, job_name
            ) latest ON cl.id = latest.max_id
            ORDER BY cl.platform, cl.job_name
        ";
        $rows = $this->db->query($sql)->result();

        $map = [];
        foreach ($rows as $row) {
            $map[$row->platform . '|' . $row->job_name] = $row;
        }
        return $map;
    }

    private function _elapsed_ms($id)
    {
        $row = $this->db->select('started_at')->where('id', $id)->get('cron_logs')->row();
        if (!$row) {
            return 0;
        }
        return (int) ((microtime(true) - strtotime($row->started_at)) * 1000);
    }
}
