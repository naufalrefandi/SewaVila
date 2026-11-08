<?php

namespace App\Http\Controllers;

use App\Mail\PropertySubmissionPaymentMail;
use App\Models\PropertySubmission;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class SubmissionController extends Controller
{
    /**
     * Show "Titip Properti / Jual Properti Anda" form
     */
    public function create(Request $request): View
    {
        $submissionFee = (float) Setting::get('submission_fee', 50000);
        $feeEnabled = (bool) Setting::get('submission_fee_enabled', 1);
        $qrisImage = Setting::getQrisUrl();
        $merchantName = Setting::get('qris_merchant_name', 'YProperti Official QRIS');
        $bcaAccount = Setting::getBcaAccount();
        $otherBankAccount = Setting::getOtherBankAccount();
        $bankAccounts = Setting::getAllBankAccounts();

        $bankName = $bcaAccount['bank_name'];
        $bankAccNum = $bcaAccount['account_number'];
        $bankAccName = $bcaAccount['account_name'];
        $instructions = Setting::get('payment_instructions', '');
        $submission = null;

        if ($request->filled('code')) {
            $submission = PropertySubmission::where('payment_code', $request->string('code')->trim())->first();
        }

        return view('pages.titip-properti', compact(
            'submissionFee',
            'feeEnabled',
            'qrisImage',
            'merchantName',
            'bcaAccount',
            'otherBankAccount',
            'bankAccounts',
            'bankName',
            'bankAccNum',
            'bankAccName',
            'instructions',
            'submission'
        ));
    }

    /**
     * Store submission from owner (Step 1 -> Generates Payment Code & Sends Payment Email)
     */
    public function store(Request $request): RedirectResponse
    {
        $submissionFee = (float) Setting::get('submission_fee', 50000);

        $rules = [
            'owner_name' => 'required|string|max:150',
            'owner_phone' => 'required|string|max:30',
            'owner_email' => 'required|email|max:150',
            'property_title' => 'required|string|max:255',
            'property_type' => 'required|string|in:rumah,apartemen,ruko,villa,tanah,gudang,hotel,gedung',
            'listing_type' => 'required|string|in:dijual,disewa',
            'expected_price' => 'required|numeric|min:1000000',
            'city' => 'required|string|max:100',
            'address' => 'required|string|max:500',
            'land_area' => 'nullable|integer|min:1',
            'building_area' => 'nullable|integer|min:1',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'certificate' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:2000',
            'payment_proof' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ];

        $validated = $request->validate($rules);

        $validated['payment_code'] = PropertySubmission::generatePaymentCode();
        $validated['fee_amount'] = $submissionFee;

        if ($request->hasFile('payment_proof') && $request->file('payment_proof')->isValid()) {
            $validated['payment_proof'] = $request->file('payment_proof')->store('submissions/proofs', 'public');
            $validated['payment_status'] = 'pending';
        } else {
            $validated['payment_status'] = 'unpaid';
        }

        $submission = PropertySubmission::create($validated);

        if (!empty($submission->owner_email)) {
            try {
                Mail::to($submission->owner_email)->send(new PropertySubmissionPaymentMail($submission));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return redirect()
            ->route('titip-properti.invoice', $submission->payment_code)
            ->with('success', "Data properti berhasil didaftarkan! Kode Pembayaran ({$submission->payment_code}) dan rincian pembayaran juga telah dikirim ke email {$submission->owner_email}. Silakan selesaikan pembayaran dan unggah bukti transfer di bawah ini.");
    }

    /**
     * Show Payment Invoice & Proof Upload Page After Titip Properti Submit
     */
    public function showPayment(string $code): View
    {
        $submission = PropertySubmission::where('payment_code', $code)->firstOrFail();
        $submissionFee = (float) ($submission->fee_amount ?: Setting::get('submission_fee', 50000));
        $feeEnabled = true;
        $qrisImage = Setting::getQrisUrl();
        $merchantName = Setting::get('qris_merchant_name', 'YProperti Official QRIS');
        $bcaAccount = Setting::getBcaAccount();
        $otherBankAccount = Setting::getOtherBankAccount();
        $bankAccounts = Setting::getAllBankAccounts();

        $bankName = $bcaAccount['bank_name'];
        $bankAccNum = $bcaAccount['account_number'];
        $bankAccName = $bcaAccount['account_name'];
        $instructions = Setting::get('payment_instructions', '');

        return view('pages.titip-properti', compact(
            'submissionFee',
            'feeEnabled',
            'qrisImage',
            'merchantName',
            'bcaAccount',
            'otherBankAccount',
            'bankAccounts',
            'bankName',
            'bankAccNum',
            'bankAccName',
            'instructions',
            'submission'
        ));
    }

    /**
     * Upload Payment Proof for Titip Properti
     */
    public function submitPaymentProof(Request $request, string $code): RedirectResponse
    {
        $submission = PropertySubmission::where('payment_code', $code)->firstOrFail();

        $request->validate([
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ], [
            'payment_proof.required' => 'Harap pilih file foto/screenshot bukti transfer.',
            'payment_proof.image' => 'File bukti transfer harus berupa gambar (JPG, PNG, WEBP).',
            'payment_proof.max' => 'Ukuran file bukti transfer maksimal 5MB.',
        ]);

        if ($request->hasFile('payment_proof') && $request->file('payment_proof')->isValid()) {
            $proofPath = $request->file('payment_proof')->store('submissions/proofs', 'public');
            $submission->update([
                'payment_proof' => $proofPath,
                'payment_status' => 'pending',
            ]);
        }

        return redirect()
            ->route('titip-properti.invoice', $submission->payment_code)
            ->with('success', "Bukti pembayaran untuk Kode {$submission->payment_code} berhasil diunggah! Tim kurasi kami akan segera memverifikasi dan menghubungi Anda.");
    }
}
