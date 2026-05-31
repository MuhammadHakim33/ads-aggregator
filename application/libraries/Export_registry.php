<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Export_registry
{
    private array $configs = [];

    public function __construct()
    {
        $CI =& get_instance();
        $CI->config->load('exporters');

        $all = $CI->config->item('exporters');
        foreach ($all as $key => $conf) {
            if ($conf['enabled']) {
                $this->configs[$key] = $conf;
            }
        }
    }

    public function configs()
    {
        return $this->configs;
    }

    public function enabled_keys()
    {
        return array_keys($this->configs);
    }

    public function has($format_key)
    {
        return isset($this->configs[$format_key]);
    }
}
