<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Property;

$props = Property::where('project_id', 9)->get();
foreach ($props as $p) {
    echo "ID: {$p->id} | {$p->title}\n";
    echo "  listing_type: {$p->listing_type}\n";
    echo "  unit_tower: '{$p->unit_tower}' | unit_floor: '{$p->unit_floor}' | unit_number: '{$p->unit_number}' | unit_type: '{$p->unit_type}' | unit_view: '{$p->unit_view}'\n";
    echo "  price: {$p->price} | sale_price: {$p->sale_price} | rent_price: {$p->rent_price} | monthly: {$p->monthly_rent_price}\n";
    echo "  building_area: {$p->building_area} | bedrooms: {$p->bedrooms} | furnished: {$p->furnished_status}\n";
    echo "--------------------------------------------------------\n";
}

