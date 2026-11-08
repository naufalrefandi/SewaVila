<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

$cols = Schema::getColumnListing('properties');
print_r(array_values(array_filter($cols, fn($c) => str_contains($c, 'unit') || str_contains($c, 'floor') || str_contains($c, 'tower') || str_contains($c, 'price') || str_contains($c, 'furnish'))));

