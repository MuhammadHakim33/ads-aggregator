<?php
defined('BASEPATH') or exit('No direct script access allowed');

$config['exporters'] = [
    'pdf' => [
        'enabled' => true,
        'class' => 'ExportPdf',
        'class_path' => APPPATH . 'libraries/ExportPdf.php',
    ],
    'excel' => [
        'enabled' => true,
        'class' => 'ExportExcel',
        'class_path' => APPPATH . 'libraries/ExportExcel.php',
    ],
];
