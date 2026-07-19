<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Client_pic_model extends CI_Model
{
    public function get_by_client_id($client_id)
    {
        $this->db->select('client_pics.*');
        $this->db->from('client_pics');
        $this->db->join('accounts', 'accounts.id = client_pics.account_id', 'left');
        $this->db->where('client_pics.client_id', $client_id);
        $this->db->order_by('client_pics.id', 'ASC');
        return $this->db->get()->result();
    }

    public function get_by_id($id)
    {
        $this->db->select('client_pics.*, accounts.email, accounts.name as username, accounts.is_active as account_active');
        $this->db->from('client_pics');
        $this->db->join('accounts', 'accounts.id = client_pics.account_id', 'left');
        $this->db->where('client_pics.id', $id);
        return $this->db->get()->row();
    }

    public function get_by_account_id($account_id)
    {
        $this->db->where('account_id', $account_id);
        return $this->db->get('client_pics')->row();
    }

    public function insert($data)
    {
        $this->db->insert('client_pics', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('client_pics', $data);
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('client_pics');
    }
}
