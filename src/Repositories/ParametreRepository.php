<?php

namespace App\Repositories;

use App\Models\Parametre;
use PDO;

class ParametreRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère tous les paramètres
     * @return Parametre[]
     */
    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM parametres ORDER BY cle ASC');
        $rows = $stmt->fetchAll();

        return array_map(fn($row) => $this->mapToParametre($row), $rows);
    }

    /**
     * Récupère un paramètre par son ID
     * @param int $id
     * @return Parametre|null
     */
    public function findById(int $id): ?Parametre
    {
        $stmt = $this->pdo->prepare('SELECT * FROM parametres WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row ? $this->mapToParametre($row) : null;
    }

    /**
     * Récupère un paramètre par sa clé
     * @param string $cle
     * @return Parametre|null
     */
    public function findByCle(string $cle): ?Parametre
    {
        $stmt = $this->pdo->prepare('SELECT * FROM parametres WHERE cle = ?');
        $stmt->execute([$cle]);
        $row = $stmt->fetch();

        return $row ? $this->mapToParametre($row) : null;
    }

    /**
     * Récupère les paramètres de jours de travail
     * @return array
     */
    public function getJoursTravail(): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM parametres WHERE cle LIKE ? ORDER BY cle ASC');
        $stmt->execute(['jours_travail_%']);
        $rows = $stmt->fetchAll();

        return array_map(fn($row) => $this->mapToParametre($row), $rows);
    }

    /**
     * Récupère les paramètres d'horaires
     * @return array
     */
    public function getHoraires(): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM parametres WHERE cle LIKE ? ORDER BY cle ASC');
        $stmt->execute(['horaire_%']);
        $rows = $stmt->fetchAll();

        return array_map(fn($row) => $this->mapToParametre($row), $rows);
    }

    /**
     * Crée un nouveau paramètre
     * @param Parametre $parametre
     * @return Parametre
     */
    public function create(Parametre $parametre): Parametre
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO parametres (cle, valeur, type, description, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?)
        ');

        $stmt->execute([
            $parametre->getCle(),
            $parametre->getValeur(),
            $parametre->getType(),
            $parametre->getDescription(),
            $parametre->getCreatedAt()->format('Y-m-d H:i:s'),
            $parametre->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);

        $parametre->setId((int)$this->pdo->lastInsertId());
        return $parametre;
    }

    /**
     * Met à jour un paramètre
     * @param Parametre $parametre
     * @return bool
     */
    public function update(Parametre $parametre): bool
    {
        $stmt = $this->pdo->prepare('
            UPDATE parametres SET
                cle = ?, valeur = ?, type = ?, description = ?, updated_at = ?
            WHERE id = ?
        ');

        return $stmt->execute([
            $parametre->getCle(),
            $parametre->getValeur(),
            $parametre->getType(),
            $parametre->getDescription(),
            (new \DateTime())->format('Y-m-d H:i:s'),
            $parametre->getId(),
        ]);
    }

    /**
     * Met à jour la valeur d'un paramètre par sa clé
     * @param string $cle
     * @param mixed $valeur
     * @return bool
     */
    public function updateValeurByCle(string $cle, $valeur): bool
    {
        $stmt = $this->pdo->prepare('
            UPDATE parametres SET valeur = ?, updated_at = ? WHERE cle = ?
        ');

        return $stmt->execute([
            (string)$valeur,
            (new \DateTime())->format('Y-m-d H:i:s'),
            $cle,
        ]);
    }

    /**
     * Met à jour plusieurs paramètres en masse
     * @param array $parametres - Tableau associatif [cle => valeur]
     * @return int Nombre de paramètres mis à jour
     */
    public function updateMultiple(array $parametres): int
    {
        $count = 0;
        foreach ($parametres as $cle => $valeur) {
            if ($this->updateValeurByCle($cle, $valeur)) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Supprime un paramètre
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM parametres WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Réinitialise un paramètre à sa valeur par défaut
     * Note: Cette méthode devrait idéalement récupérer les valeurs par défaut d'un fichier de config
     * @param string $cle
     * @return bool
     */
    public function resetToDefault(string $cle): bool
    {
        // Valeurs par défaut
        $defaults = [
            'jours_travail_lundi' => '1',
            'jours_travail_mardi' => '1',
            'jours_travail_mercredi' => '1',
            'jours_travail_jeudi' => '1',
            'jours_travail_vendredi' => '1',
            'jours_travail_samedi' => '0',
            'jours_travail_dimanche' => '0',
            'horaire_debut' => '09:00',
            'horaire_fin' => '18:00',
            'horaire_pause_duree' => '60',
            'heures_travail_par_jour' => '7',
            'heures_travail_par_semaine' => '35',
            'buffer_planification' => '0.2',
            'planification_auto_enabled' => '1',
        ];

        if (isset($defaults[$cle])) {
            return $this->updateValeurByCle($cle, $defaults[$cle]);
        }

        return false;
    }

    /**
     * Convertit une ligne de BDD en objet Parametre
     * @param array $row
     * @return Parametre
     */
    private function mapToParametre(array $row): Parametre
    {
        return new Parametre(
            cle: $row['cle'],
            valeur: $row['valeur'],
            type: $row['type'] ?? 'string',
            description: $row['description'] ?? null,
            id: (int)$row['id'],
            createdAt: new \DateTime($row['created_at']),
            updatedAt: new \DateTime($row['updated_at'])
        );
    }
}

