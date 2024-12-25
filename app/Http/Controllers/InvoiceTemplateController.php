

<?php

namespace App\Http\Controllers;

use App\Models\InvoiceTemplate;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InvoiceTemplateController extends Controller
{
    public function index()
    {
        $templates = InvoiceTemplate::where('team_id', auth()->user()->currentTeam->id)->get();
        return view('invoice-templates.index', compact('templates'));
    }

    public function create()
    {
        return view('invoice-templates.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'company_address' => 'required|string',
            'company_phone' => 'nullable|string|max:50',
            'company_email' => 'nullable|email',
            'logo' => 'nullable|image|max:2048',
            'header_text' => 'nullable|string',
            'footer_text' => 'nullable|string',
            'color_scheme' => 'required|string|max:7',
            'is_default' => 'boolean',
        ]);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logos', 'public');
            $validated['logo_path'] = $path;
        }

        $validated['team_id'] = auth()->user()->currentTeam->id;

        if ($validated['is_default']) {
            InvoiceTemplate::where('team_id', $validated['team_id'])
                ->update(['is_default' => false]);
        }

        InvoiceTemplate::create($validated);

        return redirect()->route('invoice-templates.index')
            ->with('success', 'Template created successfully');
    }

    public function edit(InvoiceTemplate $template)
    {
        $this->authorize('update', $template);
        return view('invoice-templates.form', compact('template'));
    }

    public function update(Request $request, InvoiceTemplate $template)
    {
        $this->authorize('update', $template);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'company_address' => 'required|string',
            'company_phone' => 'nullable|string|max:50',
            'company_email' => 'nullable|email',
            'logo' => 'nullable|image|max:2048',
            'header_text' => 'nullable|string',
            'footer_text' => 'nullable|string',
            'color_scheme' => 'required|string|max:7',
            'is_default' => 'boolean',
        ]);

        if ($request->hasFile('logo')) {
            if ($template->logo_path) {
                Storage::disk('public')->delete($template->logo_path);
            }
            $path = $request->file('logo')->store('logos', 'public');
            $validated['logo_path'] = $path;
        }

        if ($validated['is_default']) {
            InvoiceTemplate::where('team_id', $template->team_id)
                ->where('id', '!=', $template->id)
                ->update(['is_default' => false]);
        }

        $template->update($validated);

        return redirect()->route('invoice-templates.index')
            ->with('success', 'Template updated successfully');
    }

    public function preview(InvoiceTemplate $template)
    {
        $this->authorize('view', $template);
        
        $invoice = Invoice::where('team_id', $template->team_id)
            ->with(['customer', 'items.productService'])
            ->first();
            
        if (!$invoice) {
            $invoice = new Invoice([
                'invoice_number' => 'PREVIEW-001',
                'total_amount' => 1000.00,
                'currency' => 'USD',
                'issue_date' => now(),
                'due_date' => now()->addDays(30),
            ]);
        }

        return view('pdfs.invoice', compact('template', 'invoice'));
    }
}