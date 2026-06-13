<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Cron_log_model extends CI_Model
{
    public $table = 'cron_logs';

    public function start($job_name, $platform)
    {
        $this->db->insert($this->table, [
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
        $this->db->where('id', $id);
        $this->db->update($this->table, [
            'status' => $status,
            'rows_affected' => $rows_affected,
            'error_message' => $error_message,
            'finished_at' => date('Y-m-d H:i:s'),
            'duration_ms' => $this->_elapsed_ms($id),
        ]);
    }

    public function get_latest($limit = 50)
    {
        $this->db->order_by('id', 'DESC');
        $this->db->limit($limit);
        return $this->db->get($this->table)->result();
    }

    public function get_by_platform($platform, $limit = 20)
    {
        $this->db->where('platform', $platform);
        $this->db->order_by('id', 'DESC');
        $this->db->limit($limit);
        return $this->db->get($this->table)->result();
    }

    private function _elapsed_ms($id)
    {
        $row = $this->db->select('started_at')->where('id', $id)->get($this->table)->row();
        if (!$row) {
            return 0;
        }
        return (int) ((microtime(true) - strtotime($row->started_at)) * 1000);
    }
}
