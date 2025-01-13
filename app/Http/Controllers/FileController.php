<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class FileController extends Controller
{
    public function index(Request $request)
    {
        $files = File::query()
            ->where('user_id', auth()->id())
            ->when($request->folder_id, function ($query, $folderId) {
                $query->where('folder_id', $folderId);
            })
            ->paginate();

        return Inertia::render('Files/Index', [
            'files' => $files
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
            'folder_id' => 'nullable|exists:folders,id'
        ]);

        $file = $request->file('file');
        $path = $file->store('files', 'public');

        File::create([
            'name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'folder_id' => $request->folder_id,
            'user_id' => auth()->id(),
            'team_id' => auth()->user()->currentTeam->id,
        ]);

        return redirect()->back();
    }

    public function destroy(File $file)
    {
        $this->authorize('delete', $file);
        
        Storage::disk('public')->delete($file->path);
        $file->delete();

        return redirect()->back();
    }
}