<?php
require __DIR__ . '/vendor/autoload.php';

try {
    if (class_exists(\Filament\SpatieLaravelTranslatablePluginServiceProvider::class)) {
        echo "Class exists.\n";
    } else {
        echo "Class NOT found.\n";
    }
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
