<?php

namespace App\Http\Controllers;

use App\Models\ClientNote;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class ClientNoteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ClientNote::with(['user'])
            ->where('client_id', $request->client_id);

        // Handle content search
        if ($request->has('content')) {
            $query->where('content', 'like', "%{$request->content}%");
        }

        // Handle date range
        if ($request->has('dateRange')) {
            switch ($request->dateRange) {
                case 'today':
                    $query->whereDate('created_at', Carbon::today());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                    break;
                case 'custom':
                    if ($request->startDate && $request->endDate) {
                        $query->whereBetween('created_at', [$request->startDate, $request->endDate]);
                    }
                    break;
            }
        }

        $notes = $query->latest()->paginate(10);
        return response()->json($notes);
    }

    public function suggestions(Request $request): JsonResponse
    {
        $query = $request->get('query');
        
        $suggestions = ClientNote::where('content', 'like', "%{$query}%")
            ->select('content')
            ->distinct()
            ->limit(5)
            ->get();

        return response()->json($suggestions);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'content' => 'required|string'
        ]);

        $note = ClientNote::create([
            'client_id' => $request->client_id,
            'user_id' => auth()->id(),
            'content' => $request->content
        ]);

        return response()->json($note->load('user'));
    }

    public function destroy(ClientNote $note): JsonResponse
    {
        $note->delete();
        return response()->json(['message' => 'Note deleted successfully']);
    }
}