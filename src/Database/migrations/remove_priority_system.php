<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Database;

class RemovePrioritySystem
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function up(): void
    {
        $pdo = $this->database->getPdo();
        
        // Supprimer les colonnes de priorité
        $pdo->exec("ALTER TABLE taches DROP COLUMN IF EXISTS priorite");
        $pdo->exec("ALTER TABLE taches DROP COLUMN IF EXISTS heure_prioritaire");
        
        echo "Colonnes de priorité supprimées avec succès.\n";
    }

    public function down(): void
    {
        $pdo = $this->database->getPdo();
        
        // Recréer les colonnes de priorité
        $pdo->exec("ALTER TABLE taches ADD COLUMN priorite VARCHAR(20) DEFAULT 'normale'");
        $pdo->exec("ALTER TABLE taches ADD COLUMN heure_prioritaire BOOLEAN DEFAULT 0");
        
        echo "Colonnes de priorité restaurées avec succès.\n";
    }
}

