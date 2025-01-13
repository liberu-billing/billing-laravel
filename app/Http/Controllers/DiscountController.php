<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    public function index()
    {
        $discounts = Discount::paginate(10);
        return view('discounts.index', compact('discounts'));
    }

    public function create()
    {
        return view('discounts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|unique:discounts',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'currency' => 'required_if:type,fixed',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'max_uses' => 'nullable|integer|min:1',
        ]);

        Discount::create($validated);

        return redirect()->route('discounts.index')
            ->with('success', 'Discount created successfully');
    }

    public function edit(Discount $discount)
    {
        return view('discounts.edit', compact('discount'));
    }

    public function update(Request $request, Discount $discount)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'currency' => 'required_if:type,fixed',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'max_uses' => 'nullable|integer|min:1',
            'is_active' => 'boolean'
        ]);

        $discount->update($validated);

        return redirect()->route('discounts.index')
            ->with('success', 'Discount updated successfully');
    }

    public function destroy(Discount $discount)
    {
        $discount->delete();
        return redirect()->route('discounts.index')
            ->with('success', 'Discount deleted successfully');
    }

    public function validateCode(Request $request)
    {
        $code = $request->input('code');
        $discount = Discount::where('code', $code)->first();

        if (!$discount || !$discount->isValid()) {
            return response()->json(['valid' => false]);
        }

        return response()->json(['valid' => true, 'discount' => $discount]);
    }
}