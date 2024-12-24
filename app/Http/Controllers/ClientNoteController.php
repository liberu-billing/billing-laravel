

<?php

namespace App\Http\Controllers;

use App\Models\ClientNote;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ClientNoteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ClientNote::with(['user'])
            ->where('client_id', $request->client_id);

        if ($request->has('search')) {
            $query->where('content', 'like', "%{$request->search}%");
        }

        $notes = $query->latest()->paginate(10);

        return response()->json($notes);
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