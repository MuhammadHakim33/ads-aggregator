<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Dompdf\Dompdf;
use Dompdf\Options;

class Export_pdf
{
    public function generate($html, $filename = 'export')
    {
        // sanitize filename
        $filename = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $filename);

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

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
