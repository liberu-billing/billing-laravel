<?php

namespace App\Services;

use App\Models\Report;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportExportService
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function exportToCsv(Report $report)
    {
        $data = $this->generateReportData($report);
        $filename = sprintf('report_%s_%s.csv', $report->type, now()->format('Y-m-d'));
        
        $handle = fopen('php://temp', 'r+');
        
        // Add headers
        fputcsv($handle, array_keys(reset($data)));
        
        // Add data
        foreach ($data as $row) {
            fputcsv($handle, $row);
        }
        
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);
        
        Storage::put("reports/{$filename}", $csv);
        
        return $filename;
    }

    public function exportToPdf(Report $report)
    {
        $data = $this->generateReportData($report);
        $filename = sprintf('report_%s_%s.pdf', $report->type, now()->format('Y-m-d'));
        
        $pdf = PDF::loadView('reports.pdf', [
            'report' => $report,
            'data' => $data
        ]);
        
        Storage::put("reports/{$filename}", $pdf->output());
        
        return $filename;
    }

    private function generateReportData(Report $report)
    {
        return match($report->type) {
            'revenue' => $this->reportService->generateRevenueReport(
                $report->start_date,
                $report->end_date,
                $report->filters
            ),
            'outstanding' => $this->reportService->generateOutstandingBalanceReport(
                $report->filters
            ),
            'service' => $this->reportService->generateServiceReport(
                $report->start_date,
                $report->end_date,
                $report->filters
            ),
            default => throw new \InvalidArgumentException('Invalid report type')
        };
    }
}