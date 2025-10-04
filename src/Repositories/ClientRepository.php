<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database\Database;
use App\Models\Client;
use PDO;

class ClientRepository
{
    private Database $database;
    private PDO $pdo;

    public function __construct(Database $database)
    {
        $this->database = $database;
        $this->pdo = $database->getPdo();
    }

    public function save(Client $client): Client
    {
        if ($client->getId() === null) {
            return $this->create($client);
        } else {
            return $this->update($client);
        }
    }

    private function create(Client $client): Client
    {
        $sql = "
            INSERT INTO clients (
                nom, email, telephone, adresse, ville, code_postal, pays, 
                siret, statut, notes, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $client->getNom(),
            $client->getEmail(),
            $client->getTelephone(),
            $client->getAdresse(),
            $client->getVille(),
            $client->getCodePostal(),
            $client->getPays(),
            $client->getSiret(),
            $client->getStatut(),
            $client->getNotes(),
            $client->getCreatedAt()->format('Y-m-d H:i:s'),
            $client->getUpdatedAt()->format('Y-m-d H:i:s')
        ]);

        $client->setId((int) $this->pdo->lastInsertId());
        return $client;
    }

    private function update(Client $client): Client
    {
        $sql = "
            UPDATE clients SET
                nom = ?, email = ?, telephone = ?, adresse = ?, ville = ?,
                code_postal = ?, pays = ?, siret = ?, statut = ?, notes = ?,
                updated_at = ?
            WHERE id = ?
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $client->getNom(),
            $client->getEmail(),
            $client->getTelephone(),
            $client->getAdresse(),
            $client->getVille(),
            $client->getCodePostal(),
            $client->getPays(),
            $client->getSiret(),
            $client->getStatut(),
            $client->getNotes(),
            $client->getUpdatedAt()->format('Y-m-d H:i:s'),
            $client->getId()
        ]);

        return $client;
    }

    public function findById(int $id): ?Client
    {
        $sql = "SELECT * FROM clients WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return $this->mapToClient($data);
    }

    public function findAll(): array
    {
        $sql = "SELECT * FROM clients ORDER BY nom ASC";
        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetchAll();

        return array_map([$this, 'mapToClient'], $data);
    }

    public function findByStatut(string $statut): array
    {
        $sql = "SELECT * FROM clients WHERE statut = ? ORDER BY nom ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$statut]);
        $data = $stmt->fetchAll();

        return array_map([$this, 'mapToClient'], $data);
    }

    public function search(string $query): array
    {
        $sql = "
            SELECT * FROM clients 
            WHERE nom LIKE ? OR email LIKE ? OR ville LIKE ? OR siret LIKE ?
            ORDER BY nom ASC
        ";
        $searchTerm = "%{$query}%";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        $data = $stmt->fetchAll();

        return array_map([$this, 'mapToClient'], $data);
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM clients WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function getStatistiques(): array
    {
        $sql = "
            SELECT 
                statut,
                COUNT(*) as count
            FROM clients 
            GROUP BY statut
        ";
        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetchAll();

        $stats = [];
        foreach ($data as $row) {
            $stats[$row['statut']] = [
                'count' => (int) $row['count']
            ];
        }

        return $stats;
    }

    public function count(): int
    {
        $sql = "SELECT COUNT(*) FROM clients";
        $stmt = $this->pdo->query($sql);
        return (int) $stmt->fetchColumn();
    }

    private function mapToClient(array $data): Client
    {
        $client = new Client(
            nom: $data['nom'],
            email: $data['email'],
            telephone: $data['telephone'],
            adresse: $data['adresse'],
            ville: $data['ville'],
            codePostal: $data['code_postal'],
            pays: $data['pays'],
            siret: $data['siret'],
            statut: $data['statut'],
            notes: $data['notes'],
            id: (int) $data['id'],
            createdAt: new \DateTime($data['created_at']),
            updatedAt: new \DateTime($data['updated_at'])
        );

        return $client;
    }
}
