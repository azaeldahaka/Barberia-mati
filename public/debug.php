<?php
header('Content-Type: text/plain');
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

echo "--- DEBUG INFO ---\n";
echo "ENV APP_KEY: " . (getenv('APP_KEY') ?: 'NOT SET') . "\n";
echo "CONFIG APP_KEY: " . (config('app.key') ?: 'NOT SET') . "\n";
echo "--- LOGS ---\n";
if (file_exists(__DIR__ . '/../storage/logs/laravel.log')) {
    echo file_get_contents(__DIR__ . '/../storage/logs/laravel.log');
} else {
    echo 'No laravel.log found.';
}
