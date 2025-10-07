<?php

/**
 * Migration : Ajout du champ heure_prioritaire à la table taches
 * Date : 2025-10-06
 * Description : Permet de marquer si l'heure de début a été définie manuellement
 *               et doit être respectée lors de la re-planification automatique
 */

try {
    echo "🔄 Début de la migration : ajout de heure_prioritaire...\n";
    
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
        if ($column['name'] === 'heure_prioritaire') {
            $columnExists = true;
            break;
        }
    }
    
    if ($columnExists) {
        echo "ℹ️  La colonne heure_prioritaire existe déjà.\n";
        exit(0);
    }
    
    // Ajouter la colonne heure_prioritaire (BOOLEAN, 0=false, 1=true)
    $pdo->exec("ALTER TABLE taches ADD COLUMN heure_prioritaire BOOLEAN DEFAULT 0");
    echo "✅ Colonne heure_prioritaire ajoutée.\n";
    
    // Statistiques
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM taches");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "📊 {$count} tâche(s) dans la base de données.\n";
    
    echo "💡 Note : Les heures définies manuellement seront marquées comme prioritaires.\n";
    echo "🎉 Migration terminée avec succès !\n";
    
} catch (Exception $e) {
    echo "❌ Erreur lors de la migration : " . $e->getMessage() . "\n";
    exit(1);
}


