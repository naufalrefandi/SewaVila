<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Property;

$pd = Property::where('project_id', 9)->orWhere('title', 'like', '%Puncak Dharmahusada%')->get();
echo "Found " . $pd->count() . " units in Puncak Dharmahusada:\n";
foreach ($pd as $p) {
    echo "ID: {$p->id} | {$p->title}\n";
    echo "  Tower: {$p->tower} | Lt: {$p->floor} | Unit: {$p->unit_number} | Tipe: {$p->apartment_type} | Jual: {$p->formatted_price} | Sewa: {$p->formatted_rent_price} | Cond: {$p->furnished_status_label}\n";
}

