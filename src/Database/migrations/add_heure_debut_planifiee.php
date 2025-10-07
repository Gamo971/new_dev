<?php

/**
 * Migration : Ajout du champ heure_debut_planifiee à la table taches
 * Date : 2025-10-06
 * Description : Permet de sauvegarder l'heure de début spécifique lors du drag and drop
 *               et l'utiliser pour la re-planification automatique
 */

try {
    echo "🔄 Début de la migration : ajout de heure_debut_planifiee...\n";
    
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
        if ($column['name'] === 'heure_debut_planifiee') {
            $columnExists = true;
            break;
        }
    }
    
    if ($columnExists) {
        echo "ℹ️  La colonne heure_debut_planifiee existe déjà.\n";
        exit(0);
    }
    
    // Ajouter la colonne heure_debut_planifiee (format TIME HH:MM:SS)
    $pdo->exec("ALTER TABLE taches ADD COLUMN heure_debut_planifiee TIME");
    echo "✅ Colonne heure_debut_planifiee ajoutée.\n";
    
    // Statistiques
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM taches");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "📊 {$count} tâche(s) dans la base de données.\n";
    
    echo "💡 Note : L'heure de début sera définie lors du prochain drag and drop ou re-planification.\n";
    echo "🎉 Migration terminée avec succès !\n";
    
} catch (Exception $e) {
    echo "❌ Erreur lors de la migration : " . $e->getMessage() . "\n";
    exit(1);
}


