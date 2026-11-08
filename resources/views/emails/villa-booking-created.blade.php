<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tagihan Sewa Vila & Pembayaran QRIS</title>
</head>
<body style="font-family: 'Plus Jakarta Sans', Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 24px; color: #0f172a;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden;">
        <div style="background: linear-gradient(135deg, #db2777 0%, #be185d 100%); padding: 28px 32px; color: #ffffff;">
            <p style="margin: 0 0 6px 0; font-size: 12px; text-transform: uppercase; letter-spacing: 1.5px; color: #fbcfe8; font-weight: 700;">{{ $siteName }} • SEWA VILA</p>
            <h1 style="margin: 0; font-size: 22px; font-weight: 800;">Tagihan Pemesanan Vila</h1>
        </div>

        <div style="padding: 28px 32px;">
            <p style="margin-top: 0; font-size: 14px; line-height: 1.6; color: #334155;">
                Halo <strong>{{ $booking->customer_name }}</strong>,<br>
                Terima kasih telah memesan vila melalui <strong>{{ $siteName }}</strong>. Pemesanan Anda untuk <strong>{{ $booking->property->title }}</strong> telah berhasil dibuat. Silakan selesaikan pembayaran melalui <strong>QRIS</strong> atau transfer bank berikut:
            </p>

            <div style="background-color: #fdf2f8; border: 2px dashed #f472b6; border-radius: 12px; padding: 18px; text-align: center; margin: 20px 0;">
                <p style="margin: 0 0 4px 0; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #be185d; font-weight: 700;">Kode Booking</p>
                <p style="margin: 0; font-size: 24px; font-weight: 800; color: #0f172a; font-family: monospace;">{{ $booking->booking_code }}</p>
                <p style="margin: 8px 0 0 0; font-size: 16px; font-weight: 800; color: #db2777;">
                    Total Pembayaran: {{ $booking->formatted_grand_total }}
                </p>
            </div>

            <!-- Detail Reservasi -->
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 13px;">
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 8px 0; color: #64748b;">Vila</td>
                    <td style="padding: 8px 0; font-weight: 700; text-align: right; color: #0f172a;">{{ $booking->property->title }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 8px 0; color: #64748b;">Lokasi</td>
                    <td style="padding: 8px 0; font-weight: 600; text-align: right; color: #0f172a;">{{ $booking->property->district }}, {{ $booking->property->city }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 8px 0; color: #64748b;">Check-in</td>
                    <td style="padding: 8px 0; font-weight: 700; text-align: right; color: #0f172a;">{{ $booking->check_in_date->format('d M Y') }} (14:00)</td>
                </tr>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 8px 0; color: #64748b;">Check-out</td>
                    <td style="padding: 8px 0; font-weight: 700; text-align: right; color: #0f172a;">{{ $booking->check_out_date->format('d M Y') }} (12:00)</td>
                </tr>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 8px 0; color: #64748b;">Durasi & Tamu</td>
                    <td style="padding: 8px 0; font-weight: 600; text-align: right; color: #0f172a;">{{ $booking->total_nights }} Malam • {{ $booking->guests_count }} Tamu</td>
                </tr>
                @if($booking->cleaning_fee > 0)
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 8px 0; color: #64748b;">Biaya Kebersihan</td>
                    <td style="padding: 8px 0; font-weight: 600; text-align: right; color: #0f172a;">{{ $booking->formatted_cleaning_fee }}</td>
                </tr>
                @endif
            </table>

            <!-- QRIS Section -->
            @if(!empty($qrisImage))
            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; text-align: center; margin-bottom: 20px;">
                <p style="margin: 0 0 6px 0; font-size: 13px; font-weight: 800; color: #0f172a;">QRIS Pembayaran Resmi</p>
                <p style="margin: 0 0 12px 0; font-size: 11px; color: #64748b;">Scan dengan GoPay, OVO, DANA, BCA, Mandiri, BRI, BNI atau m-Banking apapun</p>
                <img src="{{ $qrisImage }}" alt="QRIS {{ $booking->booking_code }}" style="max-width: 220px; height: auto; border-radius: 8px; border: 1px solid #cbd5e1; display: inline-block;">
                <p style="margin: 10px 0 0 0; font-size: 12px; font-weight: 700; color: #0f172a;">Merchant: {{ $merchantName }}</p>
            </div>
            @endif

            <div style="text-align: center; margin: 26px 0 10px 0;">
                <a href="{{ $paymentUrl }}" style="display: inline-block; background: linear-gradient(135deg, #db2777 0%, #be185d 100%); color: #ffffff; text-decoration: none; font-weight: 800; font-size: 14px; padding: 14px 28px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(219, 39, 119, 0.3);">
                    Buka Halaman Pembayaran &amp; Upload Bukti &rarr;
                </a>
            </div>
        </div>
    </div>
</body>
</html>

