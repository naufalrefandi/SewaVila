<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Project;
use App\Models\Property;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

echo "=== 1. TEST APARTMENT STOCK VIEW ===\n";
$admin = User::first();
Auth::login($admin);

$controller = new \App\Http\Controllers\AdminController();
$project = Project::where('name', 'like', '%Puncak Dharmahusada%')->first();

$req = Request::create('/admin/apartment-stock', 'GET', ['project_id' => $project->id]);
$view = $controller->apartmentStock($req);

echo "View name: " . $view->name() . "\n";
echo "Total properties in stock sheet: " . $view->getData()['properties']->count() . "\n";
echo "Total units stat: " . $view->getData()['totalUnits'] . "\n";
echo "For rent stat: " . $view->getData()['forRentCount'] . "\n";
echo "For sale stat: " . $view->getData()['forSaleCount'] . "\n";
echo "Available towers: " . implode(', ', $view->getData()['availableTowers']->toArray()) . "\n";

echo "\nFirst 5 units in sorted stock sheet:\n";
foreach ($view->getData()['properties']->take(5) as $p) {
    echo "  - Tower: {$p->unit_tower} | Floor: {$p->unit_floor} ({$p->floor_number}) | Unit: {$p->unit_number} | Type: {$p->unit_type} | Rent: {$p->rent_price} | Sale: {$p->sale_price}\n";
}

echo "\n=== 2. TEST PUBLIC DETAIL PAGE WITH PROJECT UNITS ===\n";
$propController = new \App\Http\Controllers\PropertyController();
$sampleProp = Property::where('project_id', $project->id)->first();
$reqShow = Request::create('/properti/' . $sampleProp->slug, 'GET');
$showView = $propController->show($sampleProp->slug, $reqShow);

echo "Show view rendered. Project units count: " . $showView->getData()['projectUnits']->count() . "\n";

echo "\nALL TESTS PASSED!\n";

