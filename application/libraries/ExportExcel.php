<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'core/Exporter.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;

class ExportExcel implements Exporter
{
    public function generate($ad, $filename)
    {
        // sanitize filename
        $filename = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $filename);

        // instantiate spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Ad Metrics');

        // initialize header and style
        $headerBgColor = '1A2332';
        $headerFgColor = 'FFFFFF';
        $accentColor = 'E8F0FE';

        $infoRows = [
            ['Ad Title', $ad->title ?? '-'],
            ['Platform', ucfirst($ad->platform ?? '-')],
            ['Client', $ad->company_name ?? '-'],
            ['PIC', $ad->pic_name ?? '-'],
            ['Status', ($ad->is_active ? 'Active' : 'Inactive')],
            ['Generated',   date('d M Y H:i')],
        ];

        // populate info rows
        $row = 1;
        foreach ($infoRows as $info) {
            $sheet->setCellValue('A' . $row, $info[0]);
            $sheet->setCellValue('B' . $row, $info[1]);

            // style info rows
            $sheet->getStyle('A' . $row)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => '555555']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F5F5']],
            ]);
            $row++;
        }
        $row++;

        // metrics table header
        $metricsHeaderRow = $row;
        $sheet->setCellValue('A' . $row, 'Metric');
        $sheet->setCellValue('B' . $row, 'Value');
        $sheet->setCellValue('C' . $row, 'Last Updated');

        // style metrics header row
        $sheet->getStyle('A' . $row . ':C' . $row)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => $headerFgColor]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $headerBgColor]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']],
            ],
        ]);
        $row++;

        // metric rows
        if (!empty($ad->metrics)) {
            foreach ($ad->metrics as $i => $metric) {
                $bgColor = ($i % 2 === 0) ? 'FFFFFF' : $accentColor;

                $metricLabel = ucwords(str_replace('_', ' ', $metric->metric_name));
                $sheet->setCellValue('A' . $row, $metricLabel);
                $sheet->setCellValue('B' . $row, (float) $metric->metric_value);
                $sheet->setCellValue('C' . $row, date('d M Y H:i', strtotime($metric->updated_at)));

                // format value cell as number with thousand separator
                $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#,##0.##');
                // style metric rows
                $sheet->getStyle('A' . $row . ':C' . $row)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']],
                    ],
                ]);
                $row++;
            }
        } else {
            $sheet->setCellValue('A' . $row, 'No metric data available.');
            $sheet->mergeCells('A' . $row . ':C' . $row);
            $sheet->getStyle('A' . $row)->getFont()->setItalic(true)->getColor()->setRGB('999999');
        }

        // column widths
        $sheet->getColumnDimension('A')->setWidth(28);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(22);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
