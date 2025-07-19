<?php

namespace App\Http\Controllers;

use InvalidArgumentException;
use Exception;
use App\Models\LateFeeConfiguration;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LateFeeConfigurationController extends Controller
{
    public function index()
    {
        $config = LateFeeConfiguration::where('team_id', auth()->user()->currentTeam->id)->first();
        return view('late-fees.index', [
            'config' => $config,
            'frequencyOptions' => LateFeeConfiguration::getFrequencyOptions(),
            'feeTypeOptions' => LateFeeConfiguration::getFeeTypeOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fee_type' => ['required', Rule::in(['percentage', 'fixed'])],
            'fee_amount' => 'required|numeric|min:0',
            'grace_period_days' => 'required|integer|min:0',
            'max_fee_amount' => 'nullable|numeric|min:0',
            'is_compound' => 'boolean',
            'frequency' => ['required', Rule::in(['one-time', 'daily', 'weekly', 'monthly'])],
        ]);

        $validated['team_id'] = auth()->user()->currentTeam->id;

        try {
            LateFeeConfiguration::updateOrCreate(
                ['team_id' => $validated['team_id']],
                $validated
            );

            return redirect()->route('late-fees.index')
                ->with('success', 'Late fee configuration updated successfully');
        } catch (InvalidArgumentException $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function preview(Request $request)
    {
        $invoice = Invoice::findOrFail($request->invoice_id);
        $config = new LateFeeConfiguration($request->all());
        $config->team_id = auth()->user()->currentTeam->id;

        try {
            $config->validate();
            $previewAmount = $invoice->calculateLateFeePreview($config);
            return response()->json([
                'success' => true,
                'preview_amount' => $previewAmount,
                'formatted_amount' => number_format($previewAmount, 2) . ' ' . $invoice->currency
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 422);
        }
    }
}