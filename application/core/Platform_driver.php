<?php

abstract class Platform_driver
{
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->library('request');
        $this->CI->load->model('Platform_credential_model');
    }

    // contract
    abstract public function name();
    abstract public function fetch_contents($since, $until, $filters = []);
    abstract public function fetch_insights($identifiers);

    // filter capability
    public function supports_keyword_filter() { return true; }
    public function supports_hostname_filter() { return false; }
    public function supports_html_filter() { return false; }
}
