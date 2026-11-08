<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Project;
use App\Models\Property;
use App\Models\Agent;
use Illuminate\Support\Str;

$project = Project::where('name', 'like', '%Puncak Dharmahusada%')->first();
if (!$project) {
    echo "Project not found\n";
    exit;
}

$agent = Agent::first();

// 1. Update existing 10 units with realistic unit numbers if missing
$existing = Property::where('project_id', $project->id)->orderBy('id')->get();
$unitNumbers = [
    53 => 'Unit 12B',
    54 => 'Unit 08',
    55 => 'Unit 02',
    56 => 'Ruko Blok A-08',
    57 => 'Unit 11',
    58 => 'Ruko Blok B-01',
    59 => 'Unit 25',
    60 => 'Unit 06',
    61 => 'Unit 19',
    62 => 'Unit 07',
];

foreach ($existing as $p) {
    if (isset($unitNumbers[$p->id])) {
        $p->update(['unit_number' => $unitNumbers[$p->id]]);
    }
}

// 2. Add sample units directly matching the client's spreadsheet photo
$spreadsheetData = [
    [
        'tower' => 'Tower B', 'floor' => '5', 'unit' => 'Unit 508', 'type' => '2 BR',
        'rent' => 40000000, 'sale' => null, 'area' => 56, 'cond' => 'fully-furnished', 'view' => 'City View',
        'title' => 'Disewakan Apartemen Puncak Dharmahusada Tower B Lt 5 Unit 508 - 2 BR Furnished',
    ],
    [
        'tower' => 'Tower B', 'floor' => '5', 'unit' => 'Unit 512', 'type' => '2 BR',
        'rent' => 40000000, 'sale' => null, 'area' => 56, 'cond' => 'fully-furnished', 'view' => 'Pool View',
        'title' => 'Disewakan Apartemen Puncak Dharmahusada Tower B Lt 5 Unit 512 - 2 BR Siap Huni',
    ],
    [
        'tower' => 'Tower A', 'floor' => '15', 'unit' => 'Unit 1502', 'type' => '2 BR',
        'rent' => 35000000, 'sale' => null, 'area' => 48, 'cond' => 'semi-furnished', 'view' => 'City View',
        'title' => 'Disewakan Apartemen Puncak Dharmahusada Tower A Lt 15 Unit 1502 - 2 BR Semi Furnish',
    ],
    [
        'tower' => 'Tower B', 'floor' => '18', 'unit' => 'Unit 1805', 'type' => '2 BR',
        'rent' => 35000000, 'sale' => null, 'area' => 48, 'cond' => 'semi-furnished', 'view' => 'City View',
        'title' => 'Disewakan Apartemen Puncak Dharmahusada Tower B Lt 18 Unit 1805 - 2 BR City View',
    ],
    [
        'tower' => 'Tower A', 'floor' => '18', 'unit' => 'Unit 1808', 'type' => '2 BR',
        'rent' => 36000000, 'sale' => null, 'area' => 52, 'cond' => 'fully-furnished', 'view' => 'City View',
        'title' => 'Disewakan Apartemen Puncak Dharmahusada Tower A Lt 18 Unit 1808 - 2 BR Full Furnished',
    ],
    [
        'tower' => 'Tower A', 'floor' => '19', 'unit' => 'Unit 1903', 'type' => '2 BR',
        'rent' => 35000000, 'sale' => null, 'area' => 48, 'cond' => 'fully-furnished', 'view' => 'City View',
        'title' => 'Disewakan Apartemen Puncak Dharmahusada Tower A Lt 19 Unit 1903 - 2 BR Nyaman',
    ],
    [
        'tower' => 'Tower B', 'floor' => '21', 'unit' => 'Unit 2106', 'type' => '2 BR',
        'rent' => 35000000, 'sale' => null, 'area' => 48, 'cond' => 'fully-furnished', 'view' => 'Pool View',
        'title' => 'Disewakan Apartemen Puncak Dharmahusada Tower B Lt 21 Unit 2106 - 2 BR Furnished',
    ],
    [
        'tower' => 'Tower A', 'floor' => '25', 'unit' => 'Unit 2506', 'type' => '1 BR',
        'rent' => 25000000, 'sale' => null, 'area' => 24, 'cond' => 'fully-furnished', 'view' => 'City View',
        'title' => 'Disewakan Apartemen Puncak Dharmahusada Tower A Lt 25 Unit 2506 - 1 BR Cozy',
    ],
    [
        'tower' => 'Tower A', 'floor' => '32', 'unit' => 'Unit 3208', 'type' => '2 BR',
        'rent' => 40000000, 'sale' => null, 'area' => 56, 'cond' => 'fully-furnished', 'view' => 'High Floor Panoramic',
        'title' => 'Disewakan Apartemen Puncak Dharmahusada Tower A Lt 32 Unit 3208 - 2 BR High Floor View Cantik',
    ],
    [
        'tower' => 'Tower B', 'floor' => '33', 'unit' => 'Unit 3302', 'type' => '2 BR',
        'rent' => 35000000, 'sale' => null, 'area' => 48, 'cond' => 'semi-furnished', 'view' => 'City View',
        'title' => 'Disewakan Apartemen Puncak Dharmahusada Tower B Lt 33 Unit 3302 - 2 BR Semi Furnish',
    ],
];

foreach ($spreadsheetData as $row) {
    $slug = Str::slug($row['title']);
    if (Property::where('slug', $slug)->exists()) {
        continue;
    }

    Property::create([
        'title' => $row['title'],
        'slug' => $slug,
        'description' => "Disewakan unit apartemen di Apartemen Puncak Dharmahusada Surabaya. Posisi di {$row['tower']} Lantai {$row['floor']} ({$row['unit']}). Kondisi {$row['cond']}, view {$row['view']}. Siap huni, lokasi strategis dekat kampus UNAIR, ITS, dan pusat bisnis Surabaya Timur.",
        'property_type' => 'apartemen',
        'listing_type' => 'disewa',
        'price' => $row['rent'],
        'rent_price' => $row['rent'],
        'rent_period' => 'th',
        'sale_price' => null,
        'city' => 'Surabaya',
        'district' => 'Mulyorejo',
        'address' => 'Jl. Dr. Ir. H. Soekarno (MERR), Kalijudan, Kec. Mulyorejo, Surabaya',
        'latitude' => -7.262100,
        'longitude' => 112.784500,
        'bedrooms' => $row['type'] === '1 BR' ? 1 : ($row['type'] === 'Studio' ? 1 : ($row['type'] === '3 BR' ? 3 : 2)),
        'bathrooms' => 1,
        'building_area' => $row['area'],
        'land_area' => null,
        'floors' => 1,
        'furnished_status' => $row['cond'],
        'certificate' => 'Strata Title / PPJB',
        'electricity' => 2200,
        'facing' => 'Utara',
        'carports' => 1,
        'unit_tower' => $row['tower'],
        'unit_floor' => 'Lantai ' . $row['floor'],
        'unit_number' => $row['unit'],
        'unit_view' => $row['view'],
        'unit_type' => $row['type'],
        'status' => 'active',
        'is_featured' => false,
        'agent_id' => $agent->id,
        'project_id' => $project->id,
        'province_id' => $project->province_id,
        'city_id' => $project->city_id,
        'district_id' => $project->district_id,
        'images' => [
            'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=1000&q=80'
        ],
        'facilities' => ['Kolam Renang Olympic', 'Gym & Fitness Center', 'Access Card 24 Jam', 'Parkir Gedung', 'Minimarket & Foodcourt', 'ATM Center'],
        'nearby_points' => ['3 Menit ke Kampus C UNAIR', '5 Menit ke ITS Sukolilo', '5 Menit ke RS Unair & Galaxy Mall', '10 Menit ke East Coast Center Pakuwon City'],
    ]);
    echo "Added: {$row['title']}\n";
}

echo "Completed!\n";

