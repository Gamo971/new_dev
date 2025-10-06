<?php

declare(strict_types=1);

require __DIR__ . '/../../../vendor/autoload.php';

use App\Database\Database;
use App\Repositories\MissionRepository;

/**
 * Script de migration pour recalculer le temps estimé de toutes les missions
 * basé sur la somme des temps des tâches associées
 */

echo "🔄 Mise à jour du temps estimé des missions...\n\n";

try {
    // Charger les variables d'environnement
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../..');
    if (file_exists(__DIR__ . '/../../../.env')) {
        $dotenv->load();
    }

    $databasePath = $_ENV['DATABASE_PATH'] ?? __DIR__ . '/../../../storage/database.sqlite';
    $database = new Database($databasePath);
    $missionRepository = new MissionRepository($database);

    // Récupérer toutes les missions
    $pdo = $database->getPdo();
    $stmt = $pdo->query("SELECT id, nom FROM missions ORDER BY id");
    $missions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total = count($missions);
    $updated = 0;

    echo "📊 Nombre de missions à traiter : {$total}\n\n";

    foreach ($missions as $mission) {
        $missionId = (int) $mission['id'];
        $missionNom = $mission['nom'];
        
        // Calculer le nouveau temps estimé
        $tempsEstime = $missionRepository->calculateTempsEstime($missionId);
        
        // Mettre à jour la mission
        $missionRepository->updateTempsEstimeFromTaches($missionId);
        
        $heures = intval($tempsEstime / 60);
        $minutes = $tempsEstime % 60;
        $tempsFormate = $heures > 0 ? "{$heures}h {$minutes}min" : "{$minutes}min";
        
        echo "✅ Mission #{$missionId} '{$missionNom}' : {$tempsFormate}\n";
        $updated++;
    }

    echo "\n🎉 Migration terminée avec succès !\n";
    echo "   {$updated} mission(s) mise(s) à jour.\n";

} catch (\Exception $e) {
    echo "❌ Erreur lors de la migration : " . $e->getMessage() . "\n";
    exit(1);
}

