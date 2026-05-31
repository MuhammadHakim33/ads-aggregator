<?php
defined('BASEPATH') or exit('No direct script access allowed');

$config['exporters'] = [
    'pdf' => [
        'enabled'    => true,
        'class'      => 'PdfExporter',
        'class_path' => APPPATH . 'libraries/exporters/PdfExporter.php',
    ],
    'excel' => [
        'enabled'    => true,
        'class'      => 'ExcelExporter',
        'class_path' => APPPATH . 'libraries/exporters/ExcelExporter.php',
    ],
];
