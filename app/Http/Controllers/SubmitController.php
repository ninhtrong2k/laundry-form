<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SubmitController extends Controller
{
    public function submit(Request $request)
    {
        // Validate incoming form
        $data = $request->validate([
            'date_start' => 'required|date',
            'staff_name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'recipient_email' => 'required|email',

            'sheets' => 'nullable|integer|min:0',
            'sheets_notes' => 'nullable|string|max:255',

            'pillowcases' => 'nullable|integer|min:0',
            'pillowcases_notes' => 'nullable|string|max:255',

            'duvets' => 'nullable|integer|min:0',
            'duvets_notes' => 'nullable|string|max:255',

            'towels' => 'nullable|integer|min:0',
            'towels_notes' => 'nullable|string|max:255',

            'wash_cost' => 'required|numeric|min:0',
            'dry_cost' => 'required|numeric|min:0',

            'notes' => 'nullable|string',
        ]);
        // Compute totals
        $itemsTotal = 0;
        foreach (['sheets', 'pillowcases', 'duvets', 'towels'] as $k) {
            $itemsTotal += (int) ($data[$k] ?? 0);
        }
        $costTotal = (float) ($data['wash_cost'] ?? 0) + (float) ($data['dry_cost'] ?? 0);
        // Prepare email body
        $body = [];
        $body[] = "Laundry batch recorded";
        $body[] = "Submitted at: " . ($data['submitted_at'] ?? now()->toDateTimeString());
        if (!empty($data['date_start'])) {
            // Try to parse and format the date_start value for clarity
            try {
                $performed = Carbon::parse($data['date_start'])->toDateTimeString();
            } catch (\Exception $e) {
                // Fallback to raw value if parsing fails
                $performed = $data['date_start'];
            }
            $body[] = "Performed at: " . $performed;
        }
        $body[] = "Staff: " . ($data['staff_name'] ?? '');
        $body[] = "Location: " . ($data['location'] ?? '');
        $body[] = "";
        $body[] = "Items:";
        $body[] = "- Sheets: " . ($data['sheets'] ?? 0) . " " . trim($data['sheets_notes'] ?? '');
        $body[] = "- Pillowcases: " . ($data['pillowcases'] ?? 0) . " " . trim($data['pillowcases_notes'] ?? '');
        $body[] = "- Duvets: " . ($data['duvets'] ?? 0) . " " . trim($data['duvets_notes'] ?? '');
        $body[] = "- Towels: " . ($data['towels'] ?? 0) . " " . trim($data['towels_notes'] ?? '');
        $body[] = "";
        $body[] = "Total items: " . $itemsTotal;
        $body[] = "Wash cost: " . number_format($data['wash_cost'] ?? 0, 2);
        $body[] = "Dry cost: " . number_format($data['dry_cost'] ?? 0, 2);
        $body[] = "Total cost: " . number_format($costTotal, 2);
        $body[] = "";
        if (!empty($data['notes'])) {
            $body[] = "Notes: ";
            $body[] = $data['notes'];
        }
        // Add the actual send time to the message
        $body[] = "";
        $body[] = "Sent at: " . now()->toIso8601String();
        $message = implode("\n", $body);
        try {
            Mail::raw($message, function ($m) use ($data) {
                $m->to($data['recipient_email'])
                    ->subject('Laundry batch recorded - ' . Str::limit($data['staff_name'] ?? 'unknown', 40));
            });
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to send email: ' . $e->getMessage());
        }
        return redirect()->back()->with('success', 'Submission sent successfully.');
    }
}
