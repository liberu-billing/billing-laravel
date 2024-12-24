

<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function index()
    {
        $templates = EmailTemplate::where(function($query) {
            $query->where('team_id', auth()->user()->currentTeam->id)
                  ->orWhere('is_default', true);
        })->get();
        
        return view('email-templates.index', compact('templates'));
    }

    public function create()
    {
        $types = [
            'invoice_generated' => 'Invoice Generated',
            'overdue_reminder' => 'Overdue Reminder',
        ];
        return view('email-templates.create', compact('types'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $validated['team_id'] = auth()->user()->currentTeam->id;

        EmailTemplate::create($validated);

        return redirect()->route('email-templates.index')
            ->with('success', 'Template created successfully');
    }

    public function edit(EmailTemplate $template)
    {
        $this->authorize('update', $template);
        $types = [
            'invoice_generated' => 'Invoice Generated',
            'overdue_reminder' => 'Overdue Reminder',
        ];
        return view('email-templates.edit', compact('template', 'types'));
    }

    public function update(Request $request, EmailTemplate $template)
    {
        $this->authorize('update', $template);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $template->update($validated);

        return redirect()->route('email-templates.index')
            ->with('success', 'Template updated successfully');
    }
}