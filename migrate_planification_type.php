<?php

try {
    echo "🔄 Migration : ajout de planification_type...\n";
    
    $pdo = new PDO('sqlite:' . __DIR__ . '/storage/database.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Vérifier si la colonne existe déjà
    $stmt = $pdo->query("PRAGMA table_info(taches)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $columnExists = false;
    
    foreach ($columns as $column) {
        if ($column['name'] === 'planification_type') {
            $columnExists = true;
            break;
        }
    }
    
    if ($columnExists) {
        echo "ℹ️  La colonne planification_type existe déjà.\n";
        exit(0);
    }
    
    // Ajouter la colonne
    $pdo->exec("ALTER TABLE taches ADD COLUMN planification_type VARCHAR(20) DEFAULT 'automatique'");
    echo "✅ Colonne planification_type ajoutée.\n";
    
    // Statistiques
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM taches");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "📊 {$count} tâche(s) dans la base de données.\n";
    
    echo "🎉 Migration terminée avec succès !\n";
    
} catch (Exception $e) {
    echo "❌ Erreur lors de la migration : " . $e->getMessage() . "\n";
    exit(1);
}
