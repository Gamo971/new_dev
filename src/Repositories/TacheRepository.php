<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database\Database;
use App\Models\Tache;
use PDO;

class TacheRepository
{
    private Database $database;
    private PDO $pdo;

    public function __construct(Database $database)
    {
        $this->database = $database;
        $this->pdo = $database->getPdo();
    }

    public function save(Tache $tache): Tache
    {
        if ($tache->getId() === null) {
            return $this->create($tache);
        } else {
            return $this->update($tache);
        }
    }

    private function create(Tache $tache): Tache
    {
        $sql = "
            INSERT INTO taches (
                mission_id, nom, description, statut, date_echeance,
                date_planifiee, heure_debut_planifiee, planification_type, date_fin_reelle, temps_estime, temps_reel, ordre, assigne_a,
                notes, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $tache->getMissionId(),
            $tache->getNom(),
            $tache->getDescription(),
            $tache->getStatut(),
            $tache->getDateEcheance()?->format('Y-m-d'),
            $tache->getDatePlanifiee()?->format('Y-m-d'),
            $tache->getHeureDebutPlanifiee(),
            $tache->getPlanificationType(),
            $tache->getDateFinReelle()?->format('Y-m-d H:i:s'),
            $tache->getTempsEstime(),
            $tache->getTempsReel(),
            $tache->getOrdre(),
            $tache->getAssigneA(),
            $tache->getNotes(),
            $tache->getCreatedAt()->format('Y-m-d H:i:s'),
            $tache->getUpdatedAt()->format('Y-m-d H:i:s')
        ]);

        $tache->setId((int) $this->pdo->lastInsertId());
        return $tache;
    }

    private function update(Tache $tache): Tache
    {
        $sql = "
            UPDATE taches SET
                mission_id = ?, nom = ?, description = ?, statut = ?,
                date_echeance = ?, date_planifiee = ?, heure_debut_planifiee = ?, planification_type = ?, date_fin_reelle = ?, temps_estime = ?, temps_reel = ?,
                ordre = ?, assigne_a = ?, notes = ?, updated_at = ?
            WHERE id = ?
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $tache->getMissionId(),
            $tache->getNom(),
            $tache->getDescription(),
            $tache->getStatut(),
            $tache->getDateEcheance()?->format('Y-m-d'),
            $tache->getDatePlanifiee()?->format('Y-m-d'),
            $tache->getHeureDebutPlanifiee(),
            $tache->getPlanificationType(),
            $tache->getDateFinReelle()?->format('Y-m-d H:i:s'),
            $tache->getTempsEstime(),
            $tache->getTempsReel(),
            $tache->getOrdre(),
            $tache->getAssigneA(),
            $tache->getNotes(),
            $tache->getUpdatedAt()->format('Y-m-d H:i:s'),
            $tache->getId()
        ]);

        return $tache;
    }

    public function findById(int $id): ?Tache
    {
        $sql = "
            SELECT t.*, m.nom as mission_nom, c.nom as client_nom 
            FROM taches t 
            LEFT JOIN missions m ON t.mission_id = m.id 
            LEFT JOIN clients c ON m.client_id = c.id 
            WHERE t.id = ?
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return $this->mapToTache($data);
    }

    public function findByMissionId(int $missionId): array
    {
        $sql = "
            SELECT t.*, m.nom as mission_nom, c.nom as client_nom 
            FROM taches t 
            LEFT JOIN missions m ON t.mission_id = m.id 
            LEFT JOIN clients c ON m.client_id = c.id 
            WHERE t.mission_id = ? 
            ORDER BY t.ordre ASC, t.created_at ASC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$missionId]);
        $data = $stmt->fetchAll();

        return array_map([$this, 'enrichTacheData'], $data);
    }

    public function findAll(): array
    {
        $sql = "
            SELECT t.*, m.nom as mission_nom, c.nom as client_nom 
            FROM taches t 
            LEFT JOIN missions m ON t.mission_id = m.id 
            LEFT JOIN clients c ON m.client_id = c.id 
            ORDER BY t.created_at DESC
        ";
        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetchAll();

        return array_map([$this, 'enrichTacheData'], $data);
    }

    public function findByStatut(string $statut): array
    {
        $sql = "
            SELECT t.*, m.nom as mission_nom, c.nom as client_nom 
            FROM taches t 
            LEFT JOIN missions m ON t.mission_id = m.id 
            LEFT JOIN clients c ON m.client_id = c.id 
            WHERE t.statut = ? 
            ORDER BY t.date_echeance ASC, t.ordre ASC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$statut]);
        $data = $stmt->fetchAll();

        return array_map([$this, 'enrichTacheData'], $data);
    }

    public function search(string $query): array
    {
        $sql = "
            SELECT t.*, m.nom as mission_nom, c.nom as client_nom 
            FROM taches t 
            LEFT JOIN missions m ON t.mission_id = m.id 
            LEFT JOIN clients c ON m.client_id = c.id 
            WHERE t.nom LIKE ? OR t.description LIKE ? OR m.nom LIKE ? OR c.nom LIKE ?
            ORDER BY t.created_at DESC
        ";
        $searchTerm = "%{$query}%";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        $data = $stmt->fetchAll();

        return array_map([$this, 'enrichTacheData'], $data);
    }

    public function findEnRetard(): array
    {
        $sql = "
            SELECT t.*, m.nom as mission_nom, c.nom as client_nom 
            FROM taches t 
            LEFT JOIN missions m ON t.mission_id = m.id 
            LEFT JOIN clients c ON m.client_id = c.id 
            WHERE t.date_echeance < DATE('now') 
            AND t.statut NOT IN ('terminee', 'annulee')
            ORDER BY t.date_echeance ASC
        ";
        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetchAll();

        return array_map([$this, 'enrichTacheData'], $data);
    }

    public function findByAssigne(string $assigneA): array
    {
        $sql = "
            SELECT t.*, m.nom as mission_nom, c.nom as client_nom 
            FROM taches t 
            LEFT JOIN missions m ON t.mission_id = m.id 
            LEFT JOIN clients c ON m.client_id = c.id 
            WHERE t.assigne_a = ? 
            ORDER BY t.date_echeance ASC, t.ordre ASC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$assigneA]);
        $data = $stmt->fetchAll();

        return array_map([$this, 'enrichTacheData'], $data);
    }

    public function findByDatePlanifiee(string $date): array
    {
        $sql = "
            SELECT t.*, m.nom as mission_nom, c.nom as client_nom 
            FROM taches t 
            LEFT JOIN missions m ON t.mission_id = m.id 
            LEFT JOIN clients c ON m.client_id = c.id 
            WHERE t.date_planifiee = ? 
            ORDER BY t.date_echeance ASC, t.ordre ASC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$date]);
        $data = $stmt->fetchAll();

        return array_map([$this, 'enrichTacheData'], $data);
    }

    public function findPlanifieesEntreDates(string $dateDebut, string $dateFin): array
    {
        $sql = "
            SELECT t.*, m.nom as mission_nom, c.nom as client_nom 
            FROM taches t 
            LEFT JOIN missions m ON t.mission_id = m.id 
            LEFT JOIN clients c ON m.client_id = c.id 
            WHERE t.date_planifiee BETWEEN ? AND ? 
            ORDER BY t.date_planifiee ASC, t.date_echeance ASC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$dateDebut, $dateFin]);
        $data = $stmt->fetchAll();

        return array_map([$this, 'enrichTacheData'], $data);
    }

    public function findNonPlanifiees(): array
    {
        $sql = "
            SELECT t.*, m.nom as mission_nom, c.nom as client_nom 
            FROM taches t 
            LEFT JOIN missions m ON t.mission_id = m.id 
            LEFT JOIN clients c ON m.client_id = c.id 
            WHERE t.date_planifiee IS NULL 
            AND t.statut NOT IN ('terminee', 'annulee')
            ORDER BY t.date_echeance ASC, t.ordre ASC
        ";
        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetchAll();

        return array_map([$this, 'enrichTacheData'], $data);
    }

    public function findEnRetardPlanification(): array
    {
        $sql = "
            SELECT t.*, m.nom as mission_nom, c.nom as client_nom 
            FROM taches t 
            LEFT JOIN missions m ON t.mission_id = m.id 
            LEFT JOIN clients c ON m.client_id = c.id 
            WHERE t.date_planifiee < DATE('now') 
            AND t.statut NOT IN ('terminee', 'annulee')
            ORDER BY t.date_planifiee ASC, t.date_echeance ASC
        ";
        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetchAll();

        return array_map([$this, 'enrichTacheData'], $data);
    }

    public function findPlanifieesAujourdhui(): array
    {
        $sql = "
            SELECT t.*, m.nom as mission_nom, c.nom as client_nom 
            FROM taches t 
            LEFT JOIN missions m ON t.mission_id = m.id 
            LEFT JOIN clients c ON m.client_id = c.id 
            WHERE t.date_planifiee = DATE('now') 
            AND t.statut NOT IN ('terminee', 'annulee')
            ORDER BY t.date_echeance ASC, t.ordre ASC
        ";
        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetchAll();

        return array_map([$this, 'enrichTacheData'], $data);
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM taches WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function deleteByMissionId(int $missionId): bool
    {
        $sql = "DELETE FROM taches WHERE mission_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$missionId]);
    }

    public function getStatistiques(): array
    {
        $sql = "
            SELECT 
                statut,
                COUNT(*) as count,
                COALESCE(SUM(temps_estime), 0) as temps_estime,
                COALESCE(SUM(temps_reel), 0) as temps_reel
            FROM taches 
            GROUP BY statut
        ";
        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetchAll();

        $stats = [];
        foreach ($data as $row) {
            $stats[$row['statut']] = [
                'count' => (int) $row['count'],
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
                COUNT(*) as total_taches,
                COALESCE(SUM(temps_estime), 0) as temps_total_estime,
                COALESCE(SUM(temps_reel), 0) as temps_total_reel
            FROM taches
        ";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetch();
    }

    public function getStatistiquesParMission(): array
    {
        $sql = "
            SELECT 
                t.mission_id,
                m.nom as mission_nom,
                COUNT(*) as total_taches,
                COALESCE(SUM(t.temps_estime), 0) as temps_estime,
                COALESCE(SUM(t.temps_reel), 0) as temps_reel,
                SUM(CASE WHEN t.statut = 'terminee' THEN 1 ELSE 0 END) as taches_terminees
            FROM taches t
            LEFT JOIN missions m ON t.mission_id = m.id
            GROUP BY t.mission_id, m.nom
            ORDER BY total_taches DESC
        ";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    public function count(): int
    {
        $sql = "SELECT COUNT(*) FROM taches";
        $stmt = $this->pdo->query($sql);
        return (int) $stmt->fetchColumn();
    }

    public function countByMissionId(int $missionId): int
    {
        $sql = "SELECT COUNT(*) FROM taches WHERE mission_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$missionId]);
        return (int) $stmt->fetchColumn();
    }

    private function mapToTache(array $data): Tache
    {
        $tache = new Tache(
            missionId: (int) $data['mission_id'],
            nom: $data['nom'],
            description: $data['description'],
            statut: $data['statut'],
            dateEcheance: $data['date_echeance'] ? new \DateTime($data['date_echeance']) : null,
            datePlanifiee: $data['date_planifiee'] ? new \DateTime($data['date_planifiee']) : null,
            heureDebutPlanifiee: $data['heure_debut_planifiee'] ?? null,
            planificationType: $data['planification_type'] ?? 'automatique',
            dateFinReelle: $data['date_fin_reelle'] ? new \DateTime($data['date_fin_reelle']) : null,
            tempsEstime: (int) $data['temps_estime'],
            tempsReel: (int) $data['temps_reel'],
            ordre: (int) $data['ordre'],
            assigneA: $data['assigne_a'],
            notes: $data['notes'],
            id: (int) $data['id'],
            createdAt: new \DateTime($data['created_at']),
            updatedAt: new \DateTime($data['updated_at'])
        );

        return $tache;
    }

    private function enrichTacheData(array $row): array
    {
        $tache = $this->mapToTache($row);
        $array = $tache->toArray();
        $array['mission_nom'] = $row['mission_nom'] ?? null;
        $array['client_nom'] = $row['client_nom'] ?? null;
        return $array;
    }
}
