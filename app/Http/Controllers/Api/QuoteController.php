<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use App\Services\QuoteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class QuoteController extends Controller
{
    public function __construct(
        protected QuoteService $quoteService
    ) {}

    /**
     * List quotes with optional filters
     */
    public function index(Request $request): JsonResponse
    {
        $quotes = Quote::query()
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->customer_id, fn($q) => $q->where('customer_id', $request->customer_id))
            ->with(['customer'])
            ->orderByDesc('created_at')
            ->paginate($request->per_page ?? 15);

        return response()->json($quotes);
    }

    /**
     * Get a single quote
     */
    public function show(Quote $quote): JsonResponse
    {
        return response()->json([
            'data' => $quote->load(['customer', 'items']),
        ]);
    }

    /**
     * Create a new quote
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'title' => 'required|string|max:255',
            'valid_until' => 'nullable|date|after_or_equal:today',
            'currency' => 'nullable|string|size:3',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.sort_order' => 'nullable|integer',
        ]);

        $quote = $this->quoteService->createQuote($validated);

        return response()->json(['data' => $quote], Response::HTTP_CREATED);
    }

    /**
     * Update a quote
     */
    public function update(Request $request, Quote $quote): JsonResponse
    {
        if (in_array($quote->status, ['accepted', 'declined'])) {
            return response()->json([
                'message' => 'Cannot update an accepted or declined quote.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'valid_until' => 'nullable|date',
            'currency' => 'nullable|string|size:3',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'items' => 'sometimes|array|min:1',
            'items.*.description' => 'required_with:items|string',
            'items.*.quantity' => 'required_with:items|numeric|min:0.01',
            'items.*.unit_price' => 'required_with:items|numeric|min:0',
            'items.*.sort_order' => 'nullable|integer',
        ]);

        $quote = $this->quoteService->updateQuote($quote, $validated);

        return response()->json(['data' => $quote]);
    }

    /**
     * Delete a draft quote
     */
    public function destroy(Quote $quote): Response
    {
        if ($quote->status !== 'draft') {
            return response()->json([
                'message' => 'Only draft quotes can be deleted.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $quote->delete();

        return response()->noContent();
    }

    /**
     * Send a quote to the client
     */
    public function send(Quote $quote): JsonResponse
    {
        if (!in_array($quote->status, ['draft', 'sent'])) {
            return response()->json([
                'message' => 'Only draft or sent quotes can be (re)sent.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $quote = $this->quoteService->sendQuote($quote);

        return response()->json(['data' => $quote, 'message' => 'Quote sent successfully.']);
    }

    /**
     * Accept a quote
     */
    public function accept(Quote $quote): JsonResponse
    {
        if (!in_array($quote->status, ['sent', 'viewed'])) {
            return response()->json([
                'message' => 'Only sent or viewed quotes can be accepted.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $quote = $this->quoteService->acceptQuote($quote);

        return response()->json(['data' => $quote, 'message' => 'Quote accepted.']);
    }

    /**
     * Decline a quote
     */
    public function decline(Quote $quote): JsonResponse
    {
        if (!in_array($quote->status, ['sent', 'viewed'])) {
            return response()->json([
                'message' => 'Only sent or viewed quotes can be declined.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $quote = $this->quoteService->declineQuote($quote);

        return response()->json(['data' => $quote, 'message' => 'Quote declined.']);
    }

    /**
     * Convert an accepted quote to an invoice
     */
    public function convert(Quote $quote): JsonResponse
    {
        if (!$quote->canBeConverted()) {
            return response()->json([
                'message' => 'Only accepted quotes can be converted to invoices.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $invoice = $this->quoteService->convertToInvoice($quote);

        return response()->json([
            'data' => $invoice,
            'message' => 'Quote successfully converted to invoice.',
        ], Response::HTTP_CREATED);
    }

    /**
     * Get quote statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $teamId = $request->get('team_id');
        $stats = $this->quoteService->getStatistics($teamId);

        return response()->json(['data' => $stats]);
    }
}
