<?php

namespace App\Http\Controllers;

use App\Mail\AgentRegistrationPaymentMail;
use App\Models\Agent;
use App\Models\AgentApplication;
use App\Models\City;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AgentController extends Controller
{
    /**
     * Agent directory
     */
    public function index(Request $request): View
    {
        $search = $request->query('q');
        $office = $request->query('office');

        $query = Agent::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('office_branch', 'like', "%{$search}%")
                  ->orWhere('bio', 'like', "%{$search}%");
            });
        }

        if ($office) {
            $query->where('office_branch', 'like', "%{$office}%");
        }

        $agents = $query->withCount('properties')
            ->orderBy('is_top_producer', 'desc')
            ->orderBy('rating', 'desc')
            ->paginate(8)
            ->withQueryString();

        $offices = Agent::select('office_branch')->distinct()->pluck('office_branch');

        return view('pages.agents.index', compact('agents', 'offices', 'search', 'office'));
    }

    /**
     * Agent Profile & listings
     */
    public function show(string $slug): View
    {
        $agent = Agent::where('slug', $slug)->firstOrFail();

        $properties = $agent->properties()
            ->active()
            ->latest()
            ->paginate(6);

        return view('pages.agents.show', compact('agent', 'properties'));
    }

    /**
     * Show Join as Agent Form
     */
    public function joinForm(Request $request): View
    {
        $cities = City::orderBy('name')->get();
        $registrationFee = (float) Setting::get('agent_registration_fee', 100000);
        $feeEnabled = (bool) Setting::get('agent_registration_fee_enabled', 1);
        $qrisImage = Setting::getQrisUrl();
        $merchantName = Setting::get('qris_merchant_name', 'YProperti Official QRIS');
        $bcaAccount = Setting::getBcaAccount();
        $otherBankAccount = Setting::getOtherBankAccount();
        $bankAccounts = Setting::getAllBankAccounts();
        $instructions = Setting::get('payment_instructions', '');
        $application = null;

        if ($request->filled('code')) {
            $application = AgentApplication::where('payment_code', $request->string('code')->trim())->first();
        }

        return view('pages.agents.join', compact(
            'cities',
            'registrationFee',
            'feeEnabled',
            'qrisImage',
            'merchantName',
            'bcaAccount',
            'otherBankAccount',
            'bankAccounts',
            'instructions',
            'application'
        ));
    }

    /**
     * Handle Agent Registration Submission (Step 1 -> Generates Payment Code & Sends Payment Email)
     */
    public function submitJoin(Request $request): RedirectResponse
    {
        $registrationFee = (float) Setting::get('agent_registration_fee', 100000);

        $rules = [
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:agent_applications,email|unique:agents,email',
            'phone' => 'required|string|max:30',
            'city' => 'required|string|max:100',
            'office_branch_preference' => 'nullable|string|max:150',
            'experience_level' => 'required|string|in:baru,1-3_tahun,diatas_3_tahun',
            'license_number' => 'nullable|string|max:100',
            'photo' => 'nullable|string|max:500',
            'photo_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'motivation_bio' => 'nullable|string|max:2000',
            'payment_proof' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ];

        $validated = $request->validate($rules);

        if ($request->hasFile('photo_file') && $request->file('photo_file')->isValid()) {
            $path = $request->file('photo_file')->store('agents/photos', 'public');
            $validated['photo'] = $path;
        } elseif (empty($validated['photo'])) {
            $validated['photo'] = 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=300&q=80';
        }
        unset($validated['photo_file']);

        $validated['payment_code'] = AgentApplication::generatePaymentCode();
        $validated['payment_amount'] = $registrationFee;

        if ($request->hasFile('payment_proof') && $request->file('payment_proof')->isValid()) {
            $proofPath = $request->file('payment_proof')->store('agents/payment_proofs', 'public');
            $validated['payment_proof'] = $proofPath;
            $validated['payment_status'] = 'pending';
        } else {
            $validated['payment_status'] = 'unpaid';
        }

        $application = AgentApplication::create($validated);

        try {
            Mail::to($application->email)->send(new AgentRegistrationPaymentMail($application));
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()
            ->route('agents.join.invoice', $application->payment_code)
            ->with('success', "Pendaftaran berhasil dikirim! Kode Pembayaran ({$application->payment_code}) dan rincian pembayaran juga telah dikirim ke email {$application->email}. Silakan selesaikan pembayaran dan unggah bukti transfer di bawah ini.");
    }

    /**
     * Show Payment Invoice & Proof Upload Page After Registration Submit
     */
    public function showPayment(string $code): View
    {
        $application = AgentApplication::where('payment_code', $code)->firstOrFail();
        $cities = City::orderBy('name')->get();
        $registrationFee = (float) ($application->payment_amount ?: Setting::get('agent_registration_fee', 100000));
        $feeEnabled = true;
        $qrisImage = Setting::getQrisUrl();
        $merchantName = Setting::get('qris_merchant_name', 'YProperti Official QRIS');
        $bcaAccount = Setting::getBcaAccount();
        $otherBankAccount = Setting::getOtherBankAccount();
        $bankAccounts = Setting::getAllBankAccounts();
        $instructions = Setting::get('payment_instructions', '');

        return view('pages.agents.join', compact(
            'cities',
            'registrationFee',
            'feeEnabled',
            'qrisImage',
            'merchantName',
            'bcaAccount',
            'otherBankAccount',
            'bankAccounts',
            'instructions',
            'application'
        ));
    }

    /**
     * Upload Payment Proof for Agent Registration
     */
    public function submitPaymentProof(Request $request, string $code): RedirectResponse
    {
        $application = AgentApplication::where('payment_code', $code)->firstOrFail();

        $request->validate([
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ], [
            'payment_proof.required' => 'Harap pilih file foto/screenshot bukti transfer.',
            'payment_proof.image' => 'File bukti transfer harus berupa gambar (JPG, PNG, WEBP).',
            'payment_proof.max' => 'Ukuran file bukti transfer maksimal 5MB.',
        ]);

        if ($request->hasFile('payment_proof') && $request->file('payment_proof')->isValid()) {
            $proofPath = $request->file('payment_proof')->store('agents/payment_proofs', 'public');
            $application->update([
                'payment_proof' => $proofPath,
                'payment_status' => 'pending',
            ]);
        }

        return redirect()
            ->route('agents.join.invoice', $application->payment_code)
            ->with('success', "Bukti pembayaran untuk Kode {$application->payment_code} berhasil diunggah! Admin kami akan segera memverifikasi dan mengirimkan akses login (email & password) ke {$application->email}.");
    }
}
