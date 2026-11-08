<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pembayaran Berhasil - Voucher Sewa Vila</title>
</head>
<body style="font-family: 'Plus Jakarta Sans', Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 24px; color: #0f172a;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden;">
        <div style="background: linear-gradient(135deg, #059669 0%, #047857 100%); padding: 28px 32px; color: #ffffff;">
            <p style="margin: 0 0 6px 0; font-size: 12px; text-transform: uppercase; letter-spacing: 1.5px; color: #a7f3d0; font-weight: 700;">{{ $siteName }} • PEMBAYARAN LUNAS</p>
            <h1 style="margin: 0; font-size: 22px; font-weight: 800;">Reservasi Vila Terkonfirmasi! 🎉</h1>
        </div>

        <div style="padding: 28px 32px;">
            <p style="margin-top: 0; font-size: 14px; line-height: 1.6; color: #334155;">
                Halo <strong>{{ $booking->customer_name }}</strong>,<br>
                Pembayaran untuk pemesanan vila <strong>{{ $booking->property->title }}</strong> dengan kode <strong>{{ $booking->booking_code }}</strong> telah berhasil diverifikasi dan terkonfirmasi lunas!
            </p>

            <div style="background-color: #ecfdf5; border: 2px dashed #10b981; border-radius: 12px; padding: 18px; text-align: center; margin: 20px 0;">
                <p style="margin: 0 0 4px 0; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #047857; font-weight: 700;">Status Reservasi</p>
                <p style="margin: 0; font-size: 24px; font-weight: 800; color: #059669;">LUNAS &amp; TERKONFIRMASI</p>
                <p style="margin: 6px 0 0 0; font-size: 13px; color: #334155;">Kode Booking: <strong>{{ $booking->booking_code }}</strong></p>
            </div>

            <!-- Detail Reservasi -->
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 13px;">
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 8px 0; color: #64748b;">Vila</td>
                    <td style="padding: 8px 0; font-weight: 700; text-align: right; color: #0f172a;">{{ $booking->property->title }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 8px 0; color: #64748b;">Alamat</td>
                    <td style="padding: 8px 0; font-weight: 600; text-align: right; color: #0f172a;">{{ $booking->property->address }}, {{ $booking->property->city }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 8px 0; color: #64748b;">Tanggal Menginap</td>
                    <td style="padding: 8px 0; font-weight: 700; text-align: right; color: #0f172a;">
                        {{ $booking->check_in_date->format('d M Y') }} - {{ $booking->check_out_date->format('d M Y') }} ({{ $booking->total_nights }} Malam)
                    </td>
                </tr>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 8px 0; color: #64748b;">Jumlah Tamu</td>
                    <td style="padding: 8px 0; font-weight: 600; text-align: right; color: #0f172a;">{{ $booking->guests_count }} Orang</td>
                </tr>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 8px 0; color: #64748b;">Total Dibayar</td>
                    <td style="padding: 8px 0; font-weight: 800; text-align: right; color: #059669;">{{ $booking->formatted_grand_total }}</td>
                </tr>
            </table>

            <div style="text-align: center; margin: 26px 0 10px 0;">
                <a href="{{ $voucherUrl }}" style="display: inline-block; background: #059669; color: #ffffff; text-decoration: none; font-weight: 800; font-size: 14px; padding: 14px 28px; border-radius: 12px;">
                    Lihat &amp; Cetak Voucher Reservasi &rarr;
                </a>
            </div>
        </div>
    </div>
</body>
</html>

