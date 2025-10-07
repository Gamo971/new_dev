<?php

require __DIR__ . '/vendor/autoload.php';

use App\Database\Database;
use App\Database\Migrations\AddPlanificationType;

try {
    echo "🔄 Exécution de la migration AddPlanificationType...\n";
    
    $database = new Database(__DIR__ . '/storage/database.sqlite');
    $migration = new AddPlanificationType($database);
    
    $migration->up();
    
    echo "✅ Migration AddPlanificationType terminée avec succès !\n";
    
} catch (Exception $e) {
    echo "❌ Erreur lors de la migration : " . $e->getMessage() . "\n";
    exit(1);
}
