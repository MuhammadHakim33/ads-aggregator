<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'core/Exporter.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ExportExcel implements Exporter
{
    public function generate($campaign, $filename)
    {
        // sanitize filename
        $filename = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $filename);

        // instantiate spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Campaign Metrics');

        // initialize header and style
        $headerBgColor = '1A2332';
        $headerFgColor = 'FFFFFF';
        $accentColor = 'E8F0FE';

        $infoRows = [
            ['Campaign Name', ucwords($campaign->name ?? '-')],
            ['Contract Number', $campaign->contract_number ?? '-'],
            ['Client', ucwords($campaign->client_name ?? '-')],
            ['PIC', $campaign->client_pic ?? '-'],
            ['Schedule', date('d M Y', strtotime($campaign->start_date)) . ' - ' . date('d M Y', strtotime($campaign->end_date))],
            ['Status', ($campaign->is_active ? 'Active' : 'Inactive')],
            ['Generated', date('d M Y H:i')],
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
        $sheet->setCellValue('A' . $row, 'Ad Title');
        $sheet->setCellValue('B' . $row, 'Platform');
        $sheet->setCellValue('C' . $row, 'Content ID');
        $sheet->setCellValue('D' . $row, 'Metric');
        $sheet->setCellValue('E' . $row, 'Value');
        $sheet->setCellValue('F' . $row, 'Last Updated');

        // style metrics header row
        $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => $headerFgColor]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $headerBgColor]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']],
            ],
        ]);
        $row++;

        // loop ads and metrics
        $index = 0;
        if (!empty($campaign->ads)) {
            foreach ($campaign->ads as $ad) {
                if (!empty($ad->metrics)) {
                    foreach ($ad->metrics as $metric) {
                        $bgColor = ($index % 2 === 0) ? 'FFFFFF' : $accentColor;

                        $sheet->setCellValue('A' . $row, $ad->title ?? '-');
                        $sheet->setCellValue('B' . $row, ucfirst($ad->platform ?? '-'));
                        $sheet->setCellValue('C' . $row, $ad->content_identifier ?? '-');
                        $sheet->setCellValue('D' . $row, ucwords(str_replace('_', ' ', $metric->metric_name)));
                        $sheet->setCellValue('E' . $row, (float) $metric->metric_value);
                        $sheet->setCellValue('F' . $row, date('d M Y H:i', strtotime($metric->updated_at)));

                        // format value cell
                        $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0.##');

                        // style metric rows
                        $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
                            'borders' => [
                                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']],
                            ],
                        ]);
                        $row++;
                        $index++;
                    }
                } else {
                    // Ad has no metrics
                    $bgColor = ($index % 2 === 0) ? 'FFFFFF' : $accentColor;
                    $sheet->setCellValue('A' . $row, $ad->title ?? '-');
                    $sheet->setCellValue('B' . $row, ucfirst($ad->platform ?? '-'));
                    $sheet->setCellValue('C' . $row, $ad->content_identifier ?? '-');
                    $sheet->setCellValue('D' . $row, 'No metric data available.');
                    $sheet->mergeCells('D' . $row . ':F' . $row);

                    $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']],
                        ],
                    ]);
                    $row++;
                    $index++;
                }
            }
        } else {
            $sheet->setCellValue('A' . $row, 'No ads found for this campaign.');
            $sheet->mergeCells('A' . $row . ':F' . $row);
            $sheet->getStyle('A' . $row)->getFont()->setItalic(true)->getColor()->setRGB('999999');
        }

        // column widths
        $sheet->getColumnDimension('A')->setWidth(35);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(28);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(22);

        // clear any previous output or buffering to prevent file corruption
        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
