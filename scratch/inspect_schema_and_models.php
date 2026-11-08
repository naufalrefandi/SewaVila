<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "=== TABLES ===\n";
$tables = Schema::getTableListing();
print_r($tables);

echo "\n=== PROPERTY_SUBMISSIONS COLUMNS ===\n";
print_r(Schema::getColumnListing('property_submissions'));

echo "\n=== AGENTS COLUMNS ===\n";
print_r(Schema::getColumnListing('agents'));

echo "\n=== PROPERTIES COLUMNS ===\n";
print_r(Schema::getColumnListing('properties'));
