<?php
/**
 * Migration : Création de la table parametres
 * Stocke les paramètres de disponibilité de l'utilisateur
 */

require_once __DIR__ . '/../../../vendor/autoload.php';

try {
    echo "🔄 Début de la migration : création de la table parametres...\n";
    
    $databasePath = __DIR__ . '/../../../storage/database.sqlite';
    $pdo = new PDO("sqlite:" . $databasePath, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    // Vérifier si la table existe déjà
    $result = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='parametres'");
    
    if ($result->fetch()) {
        echo "ℹ️  La table 'parametres' existe déjà.\n";
        exit(0);
    }
    
    // Créer la table parametres
    $pdo->exec("
        CREATE TABLE parametres (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            cle TEXT NOT NULL UNIQUE,
            valeur TEXT,
            type TEXT DEFAULT 'string',
            description TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    echo "✅ Table 'parametres' créée avec succès.\n";
    
    // Insérer les paramètres par défaut
    $stmt = $pdo->prepare("
        INSERT INTO parametres (cle, valeur, type, description) VALUES (?, ?, ?, ?)
    ");
    
    $parametresDefaut = [
        // Jours de travail (1 = travaillé, 0 = non travaillé)
        ['jours_travail_lundi', '1', 'boolean', 'Travaille le lundi'],
        ['jours_travail_mardi', '1', 'boolean', 'Travaille le mardi'],
        ['jours_travail_mercredi', '1', 'boolean', 'Travaille le mercredi'],
        ['jours_travail_jeudi', '1', 'boolean', 'Travaille le jeudi'],
        ['jours_travail_vendredi', '1', 'boolean', 'Travaille le vendredi'],
        ['jours_travail_samedi', '0', 'boolean', 'Travaille le samedi'],
        ['jours_travail_dimanche', '0', 'boolean', 'Travaille le dimanche'],
        
        // Horaires de travail
        ['horaire_debut', '09:00', 'time', 'Heure de début de travail'],
        ['horaire_fin', '18:00', 'time', 'Heure de fin de travail'],
        ['horaire_pause_duree', '60', 'number', 'Durée de pause en minutes'],
        
        // Capacité de travail
        ['heures_travail_par_jour', '7', 'number', 'Nombre d\'heures de travail effectif par jour'],
        ['heures_travail_par_semaine', '35', 'number', 'Nombre d\'heures de travail par semaine'],
        
        // Paramètres de planification
        ['buffer_planification', '0.2', 'number', 'Buffer de sécurité (20% par défaut)'],
        ['planification_auto_enabled', '1', 'boolean', 'Activer la planification automatique'],
    ];
    
    foreach ($parametresDefaut as $param) {
        $stmt->execute($param);
        echo "  ✓ Paramètre ajouté : {$param[0]}\n";
    }
    
    echo "\n✅ Migration terminée avec succès !\n";
    echo "📊 " . count($parametresDefaut) . " paramètres par défaut créés.\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur lors de la migration : " . $e->getMessage() . "\n";
    exit(1);
}

