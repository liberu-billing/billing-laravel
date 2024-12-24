

<?php

namespace App\Services;

use App\Models\Report;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportGenerationService
{
    protected $billingService;

    public function __construct(BillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    public function generateReport(Report $report)
    {
        $data = $this->gatherReportData($report);
        
        return match($report->format) {
            'pdf' => $this->generatePdfReport($data, $report),
            'csv' => $this->generateCsvReport($data, $report),
            'excel' => $this->generateExcelReport($data, $report),
            default => throw new \Exception('Unsupported report format')
        };
    }

    protected function gatherReportData(Report $report)
    {
        return match($report->type) {
            'billing_summary' => $this->billingService->getBillingSummary($report->parameters),
            'revenue_report' => $this->billingService->getRevenueReport($report->parameters),
            'customer_activity' => $this->billingService->getCustomerActivityReport($report->parameters),
            default => throw new \Exception('Unsupported report type')
        };
    }

    protected function generatePdfReport($data, Report $report)
    {
        $pdf = PDF::loadView("reports.templates.{$report->type}", ['data' => $data]);
        $filename = "report_{$report->id}.pdf";
        Storage::put("reports/{$filename}", $pdf->output());
        return $filename;
    }

    protected function generateCsvReport($data, Report $report)
    {
        $filename = "report_{$report->id}.csv";
        $handle = fopen(Storage::path("reports/{$filename}"), 'w');
        
        fputcsv($handle, array_keys(reset($data)));
        foreach ($data as $row) {
            fputcsv($handle, $row);
        }
        
        fclose($handle);
        return $filename;
    }

    protected function generateExcelReport($data, Report $report)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $headers = array_keys(reset($data));
        foreach ($headers as $col => $header) {
            $sheet->setCellValueByColumnAndRow($col + 1, 1, $header);
        }
        
        foreach ($data as $row => $rowData) {
            foreach ($rowData as $col => $value) {
                $sheet->setCellValueByColumnAndRow($col + 1, $row + 2, $value);
            }
        }
        
        $filename = "report_{$report->id}.xlsx";
        $writer = new Xlsx($spreadsheet);
        $writer->save(Storage::path("reports/{$filename}"));
        return $filename;
    }
}