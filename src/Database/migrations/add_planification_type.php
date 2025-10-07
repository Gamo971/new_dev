<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Database;

class AddPlanificationType
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function up(): void
    {
        $pdo = $this->database->getPdo();
        
        // Ajouter la colonne planification_type
        $pdo->exec("ALTER TABLE taches ADD COLUMN planification_type VARCHAR(20) DEFAULT 'automatique'");
        
        echo "Colonne planification_type ajoutée avec succès.\n";
    }

    public function down(): void
    {
        $pdo = $this->database->getPdo();
        
        // Supprimer la colonne planification_type
        $pdo->exec("ALTER TABLE taches DROP COLUMN planification_type");
        
        echo "Colonne planification_type supprimée avec succès.\n";
    }
}

