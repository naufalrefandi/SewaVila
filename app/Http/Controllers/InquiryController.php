<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InquiryController extends Controller
{
    /**
     * Store a lead/inquiry from property detail
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'agent_id' => 'nullable|exists:agents,id',
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150',
            'phone' => 'required|string|max:30',
            'message' => 'required|string|max:2000',
            'schedule_survey_date' => 'nullable|date',
            'inquiry_type' => 'nullable|string|in:info,survey,kpr,nego',
        ]);

        $inquiry = Inquiry::create($validated);

        return back()->with('success', 'Terima kasih! Pesan dan permintaan Anda telah terkirim. Agen kami akan segera menghubungi Anda via WhatsApp atau telepon.');
    }
}
