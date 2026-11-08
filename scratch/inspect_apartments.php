<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Project;
use App\Models\Property;

echo "=== PROJECTS ===\n";
foreach (Project::withCount('properties')->get() as $p) {
    echo "ID: {$p->id} | {$p->name} | {$p->city} | {$p->properties_count} properties\n";
}

echo "\n=== SAMPLE APARTMENT PROPERTIES ===\n";
$apartments = Property::where('property_type', 'apartemen')->orWhereNotNull('project_id')->take(5)->get();
foreach ($apartments as $a) {
    echo "ID: {$a->id} | {$a->title} | ProjID: {$a->project_id} | Tower: {$a->tower} | Floor: {$a->floor} | Unit: {$a->unit_number} | Type: {$a->apartment_type} | Rent: {$a->rent_price} | Price: {$a->price} | Status: {$a->status}\n";
}

