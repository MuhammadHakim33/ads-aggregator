<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth_model extends CI_Model
{
    public $table = 'accounts';

    public function find_by_email($email)
    {
        return $this->db
            ->where('email', $email)
            ->where('is_active', 1)
            ->where('deleted_at', NULL)
            ->get($this->table)
            ->row();
    }
}
