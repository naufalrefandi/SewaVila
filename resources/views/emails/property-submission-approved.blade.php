<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Titip Properti Diverifikasi &amp; Disetujui</title>
</head>
<body style="font-family: 'Plus Jakarta Sans', Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 24px; color: #0f172a;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden;">
        <div style="background: linear-gradient(135deg, #059669 0%, #0f172a 100%); padding: 28px 32px; color: #ffffff;">
            <p style="margin: 0 0 6px 0; font-size: 12px; text-transform: uppercase; letter-spacing: 1.5px; color: #a7f3d0; font-weight: 700;">{{ $siteName }}</p>
            <h1 style="margin: 0; font-size: 22px; font-weight: 800;">Titip Properti Anda Telah Diverifikasi!</h1>
        </div>

        <div style="padding: 28px 32px;">
            <p style="margin-top: 0; font-size: 14px; line-height: 1.6; color: #334155;">
                Halo <strong>{{ $submission->owner_name }}</strong>,<br>
                Pembayaran dan pendaftaran Titip Properti Anda di <strong>{{ $siteName }}</strong> telah diverifikasi oleh Admin. Tim konsultan kami segera menindaklanjuti penayangan dan pemasaran properti Anda:
            </p>

            <div style="background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 12px; padding: 18px 20px; margin: 20px 0;">
                <p style="margin: 0 0 6px 0; font-size: 13px; color: #475569;">
                    <strong>Kode Pembayaran:</strong> <span style="font-family: monospace; color: #0f172a; font-weight: 700;">{{ $submission->payment_code }}</span>
                </p>
                <p style="margin: 0 0 6px 0; font-size: 13px; color: #475569;">
                    <strong>Judul Properti:</strong> <span style="color: #0f172a; font-weight: 700;">{{ $submission->property_title }}</span>
                </p>
                <p style="margin: 0 0 6px 0; font-size: 13px; color: #475569;">
                    <strong>Lokasi:</strong> <span style="color: #0f172a;">{{ $submission->city }} — {{ $submission->address }}</span>
                </p>
                <p style="margin: 0; font-size: 13px; color: #475569;">
                    <strong>Status Pembayaran:</strong> <span style="color: #059669; font-weight: 800;">{{ $submission->payment_status_label }}</span>
                </p>
            </div>

            <div style="text-align: center; margin-top: 24px;">
                <a href="{{ $invoiceUrl }}" style="display: inline-block; background-color: #059669; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 14px; padding: 12px 24px; border-radius: 10px;">
                    Lihat Status Titip Properti
                </a>
            </div>
        </div>
    </div>
</body>
</html>

