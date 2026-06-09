<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'core/Exporter.php';

use Dompdf\Dompdf;
use Dompdf\Options;

class ExportPdf implements Exporter
{
    public function generate($campaign, $filename)
    {
        $CI =& get_instance();
        
        // generate html from view
        $html = $CI->load->view('templates/pdf', ['campaign' => $campaign], true);
        // sanitize filename
        $filename = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $filename);
        
        // setup dompdf
        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        // instantiate and load the html
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream($filename . '.pdf', [
            'Attachment' => true,
        ]);
        
        exit;
    }
}
