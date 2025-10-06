<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database\Database;
use App\Models\Mission;
use PDO;

class MissionRepository
{
    private Database $database;
    private PDO $pdo;

    public function __construct(Database $database)
    {
        $this->database = $database;
        $this->pdo = $database->getPdo();
    }

    public function save(Mission $mission): Mission
    {
        if ($mission->getId() === null) {
            return $this->create($mission);
        } else {
            return $this->update($mission);
        }
    }

    private function create(Mission $mission): Mission
    {
        $sql = "
            INSERT INTO missions (
                client_id, nom, description, statut, priorite, date_debut,
                date_fin_prevue, date_fin_reelle, budget_prevu, budget_reel,
                temps_estime, temps_reel, notes, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $mission->getClientId(),
            $mission->getNom(),
            $mission->getDescription(),
            $mission->getStatut(),
            $mission->getPriorite(),
            $mission->getDateDebut()?->format('Y-m-d'),
            $mission->getDateFinPrevue()?->format('Y-m-d'),
            $mission->getDateFinReelle()?->format('Y-m-d H:i:s'),
            $mission->getBudgetPrevu(),
            $mission->getBudgetReel(),
            $mission->getTempsEstime(),
            $mission->getTempsReel(),
            $mission->getNotes(),
            $mission->getCreatedAt()->format('Y-m-d H:i:s'),
            $mission->getUpdatedAt()->format('Y-m-d H:i:s')
        ]);

        $mission->setId((int) $this->pdo->lastInsertId());
        return $mission;
    }

    private function update(Mission $mission): Mission
    {
        $sql = "
            UPDATE missions SET
                client_id = ?, nom = ?, description = ?, statut = ?, priorite = ?,
                date_debut = ?, date_fin_prevue = ?, date_fin_reelle = ?,
                budget_prevu = ?, budget_reel = ?, temps_estime = ?, temps_reel = ?,
                notes = ?, updated_at = ?
            WHERE id = ?
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $mission->getClientId(),
            $mission->getNom(),
            $mission->getDescription(),
            $mission->getStatut(),
            $mission->getPriorite(),
            $mission->getDateDebut()?->format('Y-m-d'),
            $mission->getDateFinPrevue()?->format('Y-m-d'),
            $mission->getDateFinReelle()?->format('Y-m-d H:i:s'),
            $mission->getBudgetPrevu(),
            $mission->getBudgetReel(),
            $mission->getTempsEstime(),
            $mission->getTempsReel(),
            $mission->getNotes(),
            $mission->getUpdatedAt()->format('Y-m-d H:i:s'),
            $mission->getId()
        ]);

        return $mission;
    }

    public function findById(int $id): ?Mission
    {
        $sql = "
            SELECT m.*, c.nom as client_nom 
            FROM missions m 
            LEFT JOIN clients c ON m.client_id = c.id 
            WHERE m.id = ?
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return $this->mapToMission($data);
    }

    public function findByClientId(int $clientId): array
    {
        $sql = "
            SELECT m.*, c.nom as client_nom 
            FROM missions m 
            LEFT JOIN clients c ON m.client_id = c.id 
            WHERE m.client_id = ? 
            ORDER BY m.created_at DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$clientId]);
        $data = $stmt->fetchAll();

        return array_map([$this, 'enrichMissionData'], $data);
    }

    public function findAll(): array
    {
        $sql = "
            SELECT m.*, c.nom as client_nom 
            FROM missions m 
            LEFT JOIN clients c ON m.client_id = c.id 
            ORDER BY m.created_at DESC
        ";
        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetchAll();

        return array_map([$this, 'enrichMissionData'], $data);
    }

    public function findByStatut(string $statut): array
    {
        $sql = "
            SELECT m.*, c.nom as client_nom 
            FROM missions m 
            LEFT JOIN clients c ON m.client_id = c.id 
            WHERE m.statut = ? 
            ORDER BY m.created_at DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$statut]);
        $data = $stmt->fetchAll();

        return array_map([$this, 'enrichMissionData'], $data);
    }

    public function search(string $query): array
    {
        $sql = "
            SELECT m.*, c.nom as client_nom 
            FROM missions m 
            LEFT JOIN clients c ON m.client_id = c.id 
            WHERE m.nom LIKE ? OR m.description LIKE ? OR c.nom LIKE ?
            ORDER BY m.created_at DESC
        ";
        $searchTerm = "%{$query}%";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        $data = $stmt->fetchAll();

        return array_map([$this, 'enrichMissionData'], $data);
    }

    public function findEnRetard(): array
    {
        $sql = "
            SELECT m.*, c.nom as client_nom 
            FROM missions m 
            LEFT JOIN clients c ON m.client_id = c.id 
            WHERE m.date_fin_prevue < DATE('now') 
            AND m.statut NOT IN ('terminee', 'annulee')
            ORDER BY m.date_fin_prevue ASC
        ";
        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetchAll();

        return array_map([$this, 'enrichMissionData'], $data);
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM missions WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function getStatistiques(): array
    {
        $sql = "
            SELECT 
                statut,
                COUNT(*) as count,
                COALESCE(SUM(budget_prevu), 0) as budget_prevu,
                COALESCE(SUM(budget_reel), 0) as budget_reel,
                COALESCE(SUM(temps_estime), 0) as temps_estime,
                COALESCE(SUM(temps_reel), 0) as temps_reel
            FROM missions 
            GROUP BY statut
        ";
        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetchAll();

        $stats = [];
        foreach ($data as $row) {
            $stats[$row['statut']] = [
                'count' => (int) $row['count'],
                'budget_prevu' => (float) $row['budget_prevu'],
                'budget_reel' => (float) $row['budget_reel'],
                'temps_estime' => (int) $row['temps_estime'],
                'temps_reel' => (int) $row['temps_reel']
            ];
        }

        return $stats;
    }

    public function getStatistiquesGlobales(): array
    {
        $sql = "
            SELECT 
                COUNT(*) as total_missions,
                COALESCE(SUM(budget_prevu), 0) as budget_total_prevu,
                COALESCE(SUM(budget_reel), 0) as budget_total_reel,
                COALESCE(SUM(temps_estime), 0) as temps_total_estime,
                COALESCE(SUM(temps_reel), 0) as temps_total_reel
            FROM missions
        ";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetch();
    }

    public function count(): int
    {
        $sql = "SELECT COUNT(*) FROM missions";
        $stmt = $this->pdo->query($sql);
        return (int) $stmt->fetchColumn();
    }

    public function countByClientId(int $clientId): int
    {
        $sql = "SELECT COUNT(*) FROM missions WHERE client_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$clientId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Calcule le temps estimé total d'une mission en fonction des tâches liées
     */
    public function calculateTempsEstime(int $missionId): int
    {
        $sql = "
            SELECT COALESCE(SUM(temps_estime), 0) as total_temps
            FROM taches
            WHERE mission_id = ?
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$missionId]);
        $result = $stmt->fetch();
        
        return (int) $result['total_temps'];
    }

    /**
     * Met à jour le temps estimé d'une mission en fonction des tâches
     */
    public function updateTempsEstimeFromTaches(int $missionId): void
    {
        $tempsEstime = $this->calculateTempsEstime($missionId);
        
        $sql = "UPDATE missions SET temps_estime = ?, updated_at = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $tempsEstime,
            (new \DateTime())->format('Y-m-d H:i:s'),
            $missionId
        ]);
    }

    private function mapToMission(array $data): Mission
    {
        $mission = new Mission(
            clientId: (int) $data['client_id'],
            nom: $data['nom'],
            description: $data['description'],
            statut: $data['statut'],
            priorite: $data['priorite'],
            dateDebut: $data['date_debut'] ? new \DateTime($data['date_debut']) : null,
            dateFinPrevue: $data['date_fin_prevue'] ? new \DateTime($data['date_fin_prevue']) : null,
            dateFinReelle: $data['date_fin_reelle'] ? new \DateTime($data['date_fin_reelle']) : null,
            budgetPrevu: $data['budget_prevu'] ? (float) $data['budget_prevu'] : null,
            budgetReel: $data['budget_reel'] ? (float) $data['budget_reel'] : null,
            tempsEstime: (int) $data['temps_estime'],
            tempsReel: (int) $data['temps_reel'],
            notes: $data['notes'],
            id: (int) $data['id'],
            createdAt: new \DateTime($data['created_at']),
            updatedAt: new \DateTime($data['updated_at'])
        );

        return $mission;
    }

    private function enrichMissionData(array $row): array
    {
        $mission = $this->mapToMission($row);
        $array = $mission->toArray();
        $array['client_nom'] = $row['client_nom'] ?? null;
        
        // Calculer le temps estimé basé sur les tâches
        if ($mission->getId() !== null) {
            $tempsEstimeTaches = $this->calculateTempsEstime($mission->getId());
            $array['temps_estime'] = $tempsEstimeTaches;
            $array['temps_estime_formate'] = $this->formatTemps($tempsEstimeTaches);
        }
        
        return $array;
    }
    
    /**
     * Formate le temps en heures et minutes
     */
    private function formatTemps(int $minutes): string
    {
        $heures = intval($minutes / 60);
        $mins = $minutes % 60;
        
        if ($heures > 0) {
            return $mins > 0 ? "{$heures}h {$mins}min" : "{$heures}h";
        }
        return "{$mins}min";
    }
}
