<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Property;

$props = Property::where('property_type', 'apartemen')->orWhereNotNull('unit_tower')->orWhereNotNull('unit_floor')->get();
echo "Total apartment properties: " . $props->count() . "\n";
foreach ($props as $p) {
    echo "ID: {$p->id} | ProjID: {$p->project_id} | Tower: '{$p->unit_tower}' | Floor: '{$p->unit_floor}' | Unit: '{$p->unit_number}' | Rent: '{$p->rent_price}' | Price: '{$p->price}'\n";
}

