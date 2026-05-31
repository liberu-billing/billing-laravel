<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Http\Resources\Api\InvoiceResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\PDF;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Invoice::class, 'invoice');
    }

    public function index(Request $request)
    {
        $invoices = Invoice::query()
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->customer_id, fn($q) => $q->where('customer_id', $request->customer_id))
            ->when($request->from_date, fn($q) => $q->where('issue_date', '>=', $request->from_date))
            ->when($request->to_date, fn($q) => $q->where('issue_date', '<=', $request->to_date))
            ->paginate($request->per_page ?? 15);
            
        return InvoiceResource::collection($invoices);
    }
    
    public function show(Invoice $invoice): \App\Http\Resources\Api\InvoiceResource
    {
        return new InvoiceResource($invoice->load(['customer', 'items']));
    }
    
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        $itemRows = array_map(fn($item) => [
            'description' => $item['description'],
            'quantity'    => $item['quantity'],
            'unit_price'  => $item['price'],
            'total_price' => $item['quantity'] * $item['price'],
            'currency'    => $validated['currency'] ?? 'USD',
        ], $validated['items']);

        $totalAmount = array_sum(array_column($itemRows, 'total_price'));

        $invoiceData = array_merge(
            collect($validated)->except('items')->toArray(),
            ['total_amount' => $totalAmount]
        );
        $invoice = new Invoice($invoiceData);
        $invoice->setAttribute('status', 'pending');
        $invoice->save();
        $invoice->refresh();

        $invoice->items()->createMany($itemRows);

        return (new InvoiceResource($invoice->load(['customer', 'items'])))->response()->setStatusCode(201);
    }
    
    public function update(Request $request, Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return response()->json([
                'message' => 'Cannot update a paid invoice'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $validated = $request->validate([
            'issue_date' => 'sometimes|date',
            'due_date' => 'sometimes|date|after_or_equal:issue_date',
            'status' => 'sometimes|in:draft,sent,paid,cancelled',
        ]);

        $invoice->update($validated);
        
        return new InvoiceResource($invoice->load(['customer', 'items']));
    }
    
    public function destroy(Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return response()->json([
                'message' => 'Only draft invoices can be deleted'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $invoice->delete();
        return response()->noContent();
    }
    
    public function download(Invoice $invoice)
    {
        $pdf = PDF::loadView('invoices.pdf', ['invoice' => $invoice->load(['customer', 'items'])]);
        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }
}