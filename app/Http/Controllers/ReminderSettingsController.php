<?php

namespace App\Http\Controllers;

use App\Models\ReminderSetting;
use Illuminate\Http\Request;

class ReminderSettingsController extends Controller
{
    public function edit()
    {
        $settings = ReminderSetting::firstOrCreate(
            ['team_id' => auth()->user()->currentTeam->id],
            [
                'days_before_reminder' => 1,
                'reminder_frequency' => 7,
                'max_reminders' => 3,
                'is_active' => true
            ]
        );
        
        return view('reminder-settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'days_before_reminder' => 'required|integer|min:1',
            'reminder_frequency' => 'required|integer|min:1',
            'max_reminders' => 'required|integer|min:1',
            'is_active' => 'boolean'
        ]);

        $settings = ReminderSetting::firstOrCreate(
            ['team_id' => auth()->user()->currentTeam->id]
        );
        
        $settings->update($validated);

        return redirect()->back()->with('success', 'Reminder settings updated successfully');
    }
}