<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Platform_credential_model extends CI_Model
{
    public $table = 'platform_credentials';

    private $cipher = 'AES-256-CBC';

    private function _get_encryption_key()
    {
        return $_ENV['CREDENTIAL_ENCRYPTION_KEY'];
    }

    private function _encrypt($json)
    {
        $key = $this->_get_encryption_key();
        if (empty($key)) {
            throw new RuntimeException('CREDENTIAL_ENCRYPTION_KEY is not set in the environment.');
        }

        $iv_length = openssl_cipher_iv_length($this->cipher);
        $iv = openssl_random_pseudo_bytes($iv_length);
        $encrypted = openssl_encrypt($json, $this->cipher, $key, OPENSSL_RAW_DATA, $iv);

        if ($encrypted === false) {
            throw new RuntimeException('Failed to encrypt credential data.');
        }

        return base64_encode($iv . $encrypted);
    }

    private function _decrypt($ciphertext)
    {
        $key = $this->_get_encryption_key();
        if (empty($key)) {
            return false;
        }

        $data = base64_decode($ciphertext, true);
        if ($data === false) {
            return false;
        }

        $iv_length = openssl_cipher_iv_length($this->cipher);
        if (strlen($data) <= $iv_length) {
            return false;
        }

        $iv = substr($data, 0, $iv_length);
        $encrypted = substr($data, $iv_length);

        return openssl_decrypt($encrypted, $this->cipher, $key, OPENSSL_RAW_DATA, $iv);
    }


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

        // attempt decryption
        $decrypted = $this->_decrypt($row->credential_data);
        if ($decrypted !== false) {
            $decoded = json_decode($decrypted, TRUE);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        // try treating the raw value as plain-text JSON
        $decoded = json_decode($row->credential_data, TRUE);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("credential_data for '{$platform}' could not be decrypted or parsed as JSON.");
        }

        return $decoded;
    }

    public function get_raw_decrypted($platform)
    {
        $decoded = $this->get_by_platform($platform);
        if (empty($decoded)) {
            return '';
        }
        return json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function is_exists($platform)
    {
        $this->db->where('platform', $platform);
        return $this->db->get($this->table)->num_rows();
    }

    public function insert($platform, $data)
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $payload = [
            'platform' => $platform,
            'credential_data' => $this->_encrypt($json),
        ];

        return $this->db->insert($this->table, $payload);
    }

    public function update($platform, $data)
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $payload = [
            'credential_data' => $this->_encrypt($json),
        ];

        $this->db->where('platform', $platform);
        return $this->db->update($this->table, $payload);
    }
}
