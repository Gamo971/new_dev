<?php

/**
 * Migration : Ajout du champ date_planifiee à la table taches
 * Date : 2025-10-05
 * Description : Permet de distinguer la date de planification (quand on prévoit de travailler)
 *               de la date d'échéance (deadline client)
 */

try {
    echo "🔄 Début de la migration : ajout de date_planifiee...\n";
    
    // Chemin de la base de données
    $databasePath = __DIR__ . '/../../../storage/database.sqlite';
    
    // Connexion directe à SQLite (sans passer par la classe Database)
    $pdo = new PDO("sqlite:" . $databasePath, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    // Vérifier si la colonne existe déjà
    $stmt = $pdo->query("PRAGMA table_info(taches)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $columnExists = false;
    
    foreach ($columns as $column) {
        if ($column['name'] === 'date_planifiee') {
            $columnExists = true;
            break;
        }
    }
    
    if ($columnExists) {
        echo "ℹ️  La colonne date_planifiee existe déjà.\n";
        exit(0);
    }
    
    // Ajouter la colonne date_planifiee
    $pdo->exec("ALTER TABLE taches ADD COLUMN date_planifiee DATE");
    echo "✅ Colonne date_planifiee ajoutée.\n";
    
    // Créer l'index pour optimiser les requêtes
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_taches_date_planifiee ON taches(date_planifiee)");
    echo "✅ Index idx_taches_date_planifiee créé.\n";
    
    // Statistiques
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM taches");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "📊 {$count} tâche(s) dans la base de données.\n";
    
    echo "🎉 Migration terminée avec succès !\n";
    
} catch (Exception $e) {
    echo "❌ Erreur lors de la migration : " . $e->getMessage() . "\n";
    exit(1);
}

