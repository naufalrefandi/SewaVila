<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kode Pembayaran Pendaftaran Agen</title>
</head>
<body style="font-family: 'Plus Jakarta Sans', Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 24px; color: #0f172a;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden;">
        <div style="background: linear-gradient(135deg, #003b88 0%, #0f172a 100%); padding: 28px 32px; color: #ffffff;">
            <p style="margin: 0 0 6px 0; font-size: 12px; text-transform: uppercase; letter-spacing: 1.5px; color: #93c5fd; font-weight: 700;">{{ $siteName }}</p>
            <h1 style="margin: 0; font-size: 22px; font-weight: 800;">Instruksi & Kode Pembayaran Agen</h1>
        </div>

        <div style="padding: 28px 32px;">
            <p style="margin-top: 0; font-size: 14px; line-height: 1.6; color: #334155;">
                Halo <strong>{{ $application->name }}</strong>,<br>
                Terima kasih telah mengirimkan pendaftaran sebagai agen mitra di <strong>{{ $siteName }}</strong>. Berikut adalah Kode Pembayaran dan rincian rekening pembayaran sesuai pengaturan resmi kami:
            </p>

            <div style="background-color: #eff6ff; border: 2px dashed #3b82f6; border-radius: 12px; padding: 18px; text-align: center; margin: 20px 0;">
                <p style="margin: 0 0 4px 0; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #1d4ed8; font-weight: 700;">Kode Pembayaran Anda</p>
                <p style="margin: 0; font-size: 24px; font-weight: 800; color: #0f172a; font-family: monospace;">{{ $application->payment_code }}</p>
                <p style="margin: 8px 0 0 0; font-size: 15px; font-weight: 700; color: #003b88;">
                    Total Tagihan: Rp {{ number_format($registrationFee, 0, ',', '.') }}
                </p>
            </div>

            <h3 style="font-size: 14px; font-weight: 800; color: #0f172a; margin-bottom: 10px; text-transform: uppercase;">Metode Pembayaran Resmi</h3>

            @if(!empty($bcaAccount['account_number']))
            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; margin-bottom: 10px;">
                <p style="margin: 0; font-size: 11px; font-weight: 700; color: #1d4ed8; text-transform: uppercase;">{{ $bcaAccount['bank_name'] }}</p>
                <p style="margin: 4px 0; font-size: 18px; font-weight: 800; color: #0f172a; font-family: monospace;">{{ $bcaAccount['account_number'] }}</p>
                <p style="margin: 0; font-size: 12px; color: #64748b;">a.n. <strong>{{ $bcaAccount['account_name'] }}</strong></p>
            </div>
            @endif

            @if(!empty($otherBankAccount) && !empty($otherBankAccount['account_number']))
            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; margin-bottom: 10px;">
                <p style="margin: 0; font-size: 11px; font-weight: 700; color: #059669; text-transform: uppercase;">{{ $otherBankAccount['bank_name'] }}</p>
                <p style="margin: 4px 0; font-size: 18px; font-weight: 800; color: #0f172a; font-family: monospace;">{{ $otherBankAccount['account_number'] }}</p>
                <p style="margin: 0; font-size: 12px; color: #64748b;">a.n. <strong>{{ $otherBankAccount['account_name'] }}</strong></p>
            </div>
            @endif

            @if(!empty($qrisImage))
            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; margin-bottom: 16px; text-align: center;">
                <p style="margin: 0 0 8px 0; font-size: 12px; font-weight: 700; color: #0f172a;">QRIS Resmi: {{ $merchantName }}</p>
                <img src="{{ $qrisImage }}" alt="QRIS" style="max-width: 180px; height: auto; border-radius: 8px; border: 1px solid #cbd5e1;">
            </div>
            @endif

            @if(!empty($instructions))
            <p style="font-size: 12px; line-height: 1.6; color: #475569; background-color: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 12px;">
                <strong>Instruksi Pembayaran:</strong><br>
                {{ $instructions }}
            </p>
            @endif

            <div style="text-align: center; margin-top: 24px;">
                <a href="{{ $invoiceUrl }}" style="display: inline-block; background-color: #003b88; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 14px; padding: 12px 24px; border-radius: 10px;">
                    Lihat Tagihan &amp; Upload Bukti Pembayaran
                </a>
            </div>
        </div>
    </div>
</body>
</html>

