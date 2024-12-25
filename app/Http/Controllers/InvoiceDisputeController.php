

<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceDispute;
use App\Services\DisputeService;
use Illuminate\Http\Request;

class InvoiceDisputeController extends Controller
{
    protected $disputeService;

    public function __construct(DisputeService $disputeService)
    {
        $this->disputeService = $disputeService;
    }

    public function store(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:255',
            'description' => 'required|string'
        ]);

        $dispute = $this->disputeService->createDispute($invoice, $validated);

        return response()->json($dispute, 201);
    }

    public function update(Request $request, InvoiceDispute $dispute)
    {
        $validated = $request->validate([
            'status' => 'required|in:under_review,resolved,rejected',
            'resolution_notes' => 'required_if:status,resolved,rejected|string'
        ]);

        $dispute = $this->disputeService->updateDisputeStatus(
            $dispute,
            $validated['status'],
            $validated['resolution_notes'] ?? null
        );

        return response()->json($dispute);
    }

    public function addMessage(Request $request, InvoiceDispute $dispute)
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'attachments' => 'nullable|array'
        ]);

        $message = $this->disputeService->addMessage($dispute, $validated);

        return response()->json($message, 201);
    }
}