<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Services\ReportGenerationService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(protected ReportGenerationService $reportService) {}

    public function index(): Factory|View
    {
        $reports = Report::where(
            'team_id',
            auth()->user()->currentTeam->id
        )->get();

        return view(
            'reports.index',
            compact('reports')
        );
    }

    public function create(): Factory|View
    {
        return view('reports.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'name' => 'required|string',
                'type' => 'required|string',
                'format' => 'required|in:pdf,csv,excel',
                'parameters' => 'required|array',
                'schedule' => 'nullable|array',
            ]
        );

        $report = Report::create(
            [
                ...$validated,
                'team_id' => auth()->user()->currentTeam->id,
            ]
        );

        if ($request->generate_now) {
            $filename = $this->reportService->generateReport($report);

            return Storage::download("reports/{$filename}");
        }

        return redirect()->route('reports.index');
    }

    public function generate(Report $report): StreamedResponse
    {
        $filename = $this->reportService->generateReport($report);

        return Storage::download("reports/{$filename}");
    }
}
