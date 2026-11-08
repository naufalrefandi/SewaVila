<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Akses Login Agen Disetujui</title>
</head>
<body style="font-family: 'Plus Jakarta Sans', Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 24px; color: #0f172a;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden;">
        <div style="background: linear-gradient(135deg, #059669 0%, #0f172a 100%); padding: 28px 32px; color: #ffffff;">
            <p style="margin: 0 0 6px 0; font-size: 12px; text-transform: uppercase; letter-spacing: 1.5px; color: #a7f3d0; font-weight: 700;">{{ $siteName }}</p>
            <h1 style="margin: 0; font-size: 22px; font-weight: 800;">Selamat! Akun Agen Anda Telah Aktif</h1>
        </div>

        <div style="padding: 28px 32px;">
            <p style="margin-top: 0; font-size: 14px; line-height: 1.6; color: #334155;">
                Halo <strong>{{ $agent->name }}</strong>,<br>
                Pendaftaran Anda sebagai agen di <strong>{{ $siteName }}</strong> telah disetujui oleh Admin. Berikut adalah informasi kredensial untuk masuk ke Dashboard Agen Anda:
            </p>

            <div style="background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 12px; padding: 18px 20px; margin: 20px 0;">
                <p style="margin: 0 0 8px 0; font-size: 13px; color: #475569;">
                    <strong>Email Login:</strong> <span style="font-family: monospace; color: #0f172a; font-weight: 700;">{{ $agent->email }}</span>
                </p>
                <p style="margin: 0; font-size: 13px; color: #475569;">
                    <strong>Password Sementara:</strong> <span style="font-family: monospace; font-size: 16px; background-color: #fef3c7; color: #92400e; padding: 3px 8px; border-radius: 6px; font-weight: 800;">{{ $plainPassword }}</span>
                </p>
            </div>

            <div style="background-color: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; padding: 14px 16px; margin-bottom: 24px;">
                <p style="margin: 0; font-size: 13px; line-height: 1.5; color: #991b1b; font-weight: 700;">
                    ⚠️ Penting: Harap Segera Ganti Password Anda!
                </p>
                <p style="margin: 6px 0 0 0; font-size: 12px; line-height: 1.5; color: #b91c1c;">
                    Demi keamanan akun Anda, password sementara di atas <strong>wajib diganti sesegera mungkin</strong> setelah Anda berhasil login melalui menu <strong>Profil Saya &amp; Ganti Password</strong>.
                </p>
            </div>

            <div style="text-align: center; margin-top: 24px;">
                <a href="{{ $loginUrl }}" style="display: inline-block; background-color: #003b88; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 14px; padding: 12px 24px; border-radius: 10px;">
                    Login ke Dashboard Agen Sekarang
                </a>
            </div>
        </div>
    </div>
</body>
</html>

