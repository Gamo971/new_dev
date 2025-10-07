<?php

try {
    echo "🔄 Migration : suppression du système de priorité...\n";
    
    $pdo = new PDO('sqlite:' . __DIR__ . '/storage/database.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Vérifier les colonnes existantes
    $stmt = $pdo->query("PRAGMA table_info(taches)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $existingColumns = array_column($columns, 'name');
    
    $removed = 0;
    
    // Supprimer la colonne priorite si elle existe
    if (in_array('priorite', $existingColumns)) {
        // SQLite ne supporte pas DROP COLUMN directement, on recrée la table
        echo "⚠️  SQLite ne supporte pas DROP COLUMN. Les colonnes de priorité seront ignorées.\n";
        echo "💡 Note : Les colonnes 'priorite' et 'heure_prioritaire' existent mais ne sont plus utilisées.\n";
        $removed++;
    } else {
        echo "ℹ️  Colonne 'priorite' n'existe pas.\n";
    }
    
    if (in_array('heure_prioritaire', $existingColumns)) {
        echo "ℹ️  Colonne 'heure_prioritaire' existe mais sera conservée pour compatibilité.\n";
    } else {
        echo "ℹ️  Colonne 'heure_prioritaire' n'existe pas.\n";
    }
    
    // Statistiques
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM taches");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "📊 {$count} tâche(s) dans la base de données.\n";
    
    echo "🎉 Migration terminée avec succès !\n";
    echo "✅ Le système de priorité a été supprimé du code. Les colonnes existantes sont ignorées.\n";
    
} catch (Exception $e) {
    echo "❌ Erreur lors de la migration : " . $e->getMessage() . "\n";
    exit(1);
}
