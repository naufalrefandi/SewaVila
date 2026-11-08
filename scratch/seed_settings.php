<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Setting;
use App\Models\Agent;

echo "=== SEEDING SETTINGS ===\n";
Setting::set('submission_fee', 50000, 'pricing');
Setting::set('submission_fee_enabled', '1', 'pricing');
Setting::set('agent_free_quota', 3, 'pricing');
Setting::set('agent_topup_price', 100000, 'pricing');
Setting::set('agent_topup_quota', 5, 'pricing');
Setting::set('featured_listing_price', 50000, 'pricing');
Setting::set('featured_duration_days', 30, 'pricing');
Setting::set('qris_merchant_name', 'PropNex Indonesia Official QRIS', 'payment');
Setting::set('bank_name', 'BCA (Bank Central Asia)', 'payment');
Setting::set('bank_account_number', '8290-9988-7766', 'payment');
Setting::set('bank_account_name', 'PT PROPNEX REALTY INDONESIA', 'payment');
Setting::set('payment_instructions', 'Scan QRIS di atas menggunakan m-Banking (BCA, Mandiri, BRI, BNI) atau E-Wallet (GoPay, OVO, Dana, ShopeePay). Simpan dan unggah struk/bukti transfer untuk verifikasi admin.', 'payment');

echo "Settings seeded:\n";
foreach (Setting::all() as $s) {
    echo "  {$s->key} = {$s->value}\n";
}

echo "\n=== UPDATING AGENT QUOTA ===\n";
foreach (Agent::all() as $a) {
    // Give existing agents at least max(3, current_count + 3) so existing seeded properties don't get locked out
    $currentProps = $a->properties()->count();
    $a->update([
        'listing_quota' => max(3, $currentProps + 3)
    ]);
    echo "Agent {$a->name}: properties={$currentProps}, quota={$a->listing_quota}, remaining={$a->remaining_quota}\n";
}
echo "Done!\n";

