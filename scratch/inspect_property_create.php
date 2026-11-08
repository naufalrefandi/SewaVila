<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ref = new ReflectionMethod(\App\Http\Controllers\AdminController::class, 'storeProperty');
echo "storeProperty: lines " . $ref->getStartLine() . " to " . $ref->getEndLine() . "\n";

$ref2 = new ReflectionMethod(\App\Http\Controllers\AdminController::class, 'createProperty');
echo "createProperty: lines " . $ref2->getStartLine() . " to " . $ref2->getEndLine() . "\n";

