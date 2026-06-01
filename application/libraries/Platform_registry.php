<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Platform_registry
{
    private array $configs = [];
    protected $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->config->load('platforms');

        $all = $this->CI->config->item('platforms');
        foreach ($all as $name => $conf) {
            if ($conf['enabled']) {
                $this->configs[$name] = $conf;
            }
        }
    }

    public function configs()
    {
        return $this->configs;
    }

    public function enabled_names()
    {
        return array_keys($this->configs);
    }

    public function config($name)
    {
        return $this->configs[$name] ?? null;
    }
   
    public function names_as_list()
    {
        return implode(',', $this->enabled_names());
    }
}