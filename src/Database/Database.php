<?php

declare(strict_types=1);

namespace App\Database;

use PDO;
use PDOException;

class Database
{
    private PDO $pdo;
    private string $databasePath;

    public function __construct(string $databasePath)
    {
        $this->databasePath = $databasePath;
        $this->initializeDatabase();
    }

    private function initializeDatabase(): void
    {
        try {
            // Créer le dossier storage s'il n'existe pas
            $storageDir = dirname($this->databasePath);
            if (!is_dir($storageDir)) {
                mkdir($storageDir, 0755, true);
            }

            // Connexion à SQLite
            $dsn = "sqlite:" . $this->databasePath;
            $this->pdo = new PDO($dsn, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            // Activer les clés étrangères
            $this->pdo->exec('PRAGMA foreign_keys = ON');

            // Créer les tables
            $this->createTables();
        } catch (PDOException $e) {
            throw new DatabaseException("Erreur de connexion à la base de données: " . $e->getMessage());
        }
    }

    private function createTables(): void
    {
        $sql = "
            -- Table des clients
            CREATE TABLE IF NOT EXISTS clients (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nom VARCHAR(255) NOT NULL,
                email VARCHAR(255),
                telephone VARCHAR(50),
                adresse TEXT,
                ville VARCHAR(100),
                code_postal VARCHAR(10),
                pays VARCHAR(100) DEFAULT 'France',
                siret VARCHAR(20),
                statut TEXT DEFAULT 'actif' CHECK (statut IN ('actif', 'inactif', 'suspendu')),
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            -- Table des contacts
            CREATE TABLE IF NOT EXISTS contacts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                client_id INTEGER NOT NULL,
                prenom VARCHAR(100) NOT NULL,
                nom VARCHAR(100) NOT NULL,
                email VARCHAR(255),
                telephone VARCHAR(50),
                poste VARCHAR(100),
                statut TEXT DEFAULT 'actif' CHECK (statut IN ('actif', 'inactif')),
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
            );

            -- Table des missions
            CREATE TABLE IF NOT EXISTS missions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                client_id INTEGER NOT NULL,
                nom VARCHAR(255) NOT NULL,
                description TEXT,
                statut TEXT DEFAULT 'en_attente' CHECK (statut IN ('en_attente', 'en_cours', 'en_pause', 'terminee', 'annulee')),
                priorite TEXT DEFAULT 'normale' CHECK (priorite IN ('basse', 'normale', 'haute', 'urgente')),
                date_debut DATE,
                date_fin_prevue DATE,
                date_fin_reelle DATE,
                budget_prevu DECIMAL(10,2),
                budget_reel DECIMAL(10,2),
                temps_estime INTEGER DEFAULT 0,
                temps_reel INTEGER DEFAULT 0,
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
            );

            -- Table des tâches
            CREATE TABLE IF NOT EXISTS taches (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                mission_id INTEGER NOT NULL,
                nom VARCHAR(255) NOT NULL,
                description TEXT,
                statut TEXT DEFAULT 'a_faire' CHECK (statut IN ('a_faire', 'en_cours', 'terminee', 'annulee')),
                priorite TEXT DEFAULT 'normale' CHECK (priorite IN ('basse', 'normale', 'haute', 'urgente')),
                date_echeance DATE,
                date_fin_reelle DATETIME,
                temps_estime INTEGER DEFAULT 0,
                temps_reel INTEGER DEFAULT 0,
                ordre INTEGER DEFAULT 0,
                assigne_a VARCHAR(255),
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (mission_id) REFERENCES missions(id) ON DELETE CASCADE
            );

            -- Index pour améliorer les performances
            CREATE INDEX IF NOT EXISTS idx_clients_nom ON clients(nom);
            CREATE INDEX IF NOT EXISTS idx_contacts_client_id ON contacts(client_id);
            CREATE INDEX IF NOT EXISTS idx_missions_client_id ON missions(client_id);
            CREATE INDEX IF NOT EXISTS idx_missions_statut ON missions(statut);
            CREATE INDEX IF NOT EXISTS idx_taches_mission_id ON taches(mission_id);
            CREATE INDEX IF NOT EXISTS idx_taches_statut ON taches(statut);
            CREATE INDEX IF NOT EXISTS idx_taches_date_echeance ON taches(date_echeance);
        ";
        
        $this->pdo->exec($sql);
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    public function getDatabasePath(): string
    {
        return $this->databasePath;
    }

    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    public function rollback(): bool
    {
        return $this->pdo->rollBack();
    }
}

class DatabaseException extends \Exception
{
}
