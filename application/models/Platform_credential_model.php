<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Platform_credential_model extends CI_Model
{
    public $table = 'platform_credentials';

    public function get_all()
    {
        $this->db->select('*');
        $this->db->from($this->table);
        return $this->db->get()->result();
    }

    public function get_by_platform($platform)
    {
        $this->db->where('platform', $platform);
        $row = $this->db->get($this->table)->row();

        if (!$row) {
            return [];
        }

        // decode credential data from json
        $decoded = json_decode($row->credential_data, TRUE);
        // check if json is valid
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("credential_data for '{$platform}' is not valid JSON.");
        }

        return $decoded;
    }

    public function is_exists($platform)
    {
        $this->db->where('platform', $platform);
        return $this->db->get($this->table)->num_rows();
    }

    public function insert($platform, $data)
    {
        $payload = [
            'platform' => $platform,
            'credential_data' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
        ];

        $this->db->insert($this->table, $payload);
        return $this->db->insert_id();
    }

    public function update($platform, $data)
    {
        $payload = [
            'platform' => $platform,
            'credential_data' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
        ];

        $this->db->where('platform', $platform);
        $this->db->update($this->table, $payload);
        return $this->db->affected_rows() > 0;
    }
}
