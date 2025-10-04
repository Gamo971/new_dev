<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database\Database;
use App\Models\Contact;
use PDO;

class ContactRepository
{
    private Database $database;
    private PDO $pdo;

    public function __construct(Database $database)
    {
        $this->database = $database;
        $this->pdo = $database->getPdo();
    }

    public function save(Contact $contact): Contact
    {
        if ($contact->getId() === null) {
            return $this->create($contact);
        } else {
            return $this->update($contact);
        }
    }

    private function create(Contact $contact): Contact
    {
        $sql = "
            INSERT INTO contacts (
                client_id, prenom, nom, email, telephone, poste, 
                statut, notes, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $contact->getClientId(),
            $contact->getPrenom(),
            $contact->getNom(),
            $contact->getEmail(),
            $contact->getTelephone(),
            $contact->getPoste(),
            $contact->getStatut(),
            $contact->getNotes(),
            $contact->getCreatedAt()->format('Y-m-d H:i:s'),
            $contact->getUpdatedAt()->format('Y-m-d H:i:s')
        ]);

        $contact->setId((int) $this->pdo->lastInsertId());
        return $contact;
    }

    private function update(Contact $contact): Contact
    {
        $sql = "
            UPDATE contacts SET
                client_id = ?, prenom = ?, nom = ?, email = ?, telephone = ?,
                poste = ?, statut = ?, notes = ?, updated_at = ?
            WHERE id = ?
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $contact->getClientId(),
            $contact->getPrenom(),
            $contact->getNom(),
            $contact->getEmail(),
            $contact->getTelephone(),
            $contact->getPoste(),
            $contact->getStatut(),
            $contact->getNotes(),
            $contact->getUpdatedAt()->format('Y-m-d H:i:s'),
            $contact->getId()
        ]);

        return $contact;
    }

    public function findById(int $id): ?Contact
    {
        $sql = "SELECT * FROM contacts WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return $this->mapToContact($data);
    }

    public function findByClientId(int $clientId): array
    {
        $sql = "SELECT * FROM contacts WHERE client_id = ? ORDER BY nom, prenom ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$clientId]);
        $data = $stmt->fetchAll();

        return array_map([$this, 'enrichContactData'], $data);
    }

    public function findAll(): array
    {
        $sql = "
            SELECT c.*, cl.nom as client_nom 
            FROM contacts c 
            LEFT JOIN clients cl ON c.client_id = cl.id 
            ORDER BY c.nom, c.prenom ASC
        ";
        $stmt = $this->pdo->query($sql);
        $data = $stmt->fetchAll();

        return array_map([$this, 'enrichContactData'], $data);
    }

    public function findByStatut(string $statut): array
    {
        $sql = "
            SELECT c.*, cl.nom as client_nom 
            FROM contacts c 
            LEFT JOIN clients cl ON c.client_id = cl.id 
            WHERE c.statut = ? 
            ORDER BY c.nom, c.prenom ASC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$statut]);
        $data = $stmt->fetchAll();

        return array_map([$this, 'enrichContactData'], $data);
    }

    public function search(string $query): array
    {
        $sql = "
            SELECT c.*, cl.nom as client_nom 
            FROM contacts c 
            LEFT JOIN clients cl ON c.client_id = cl.id 
            WHERE c.prenom LIKE ? OR c.nom LIKE ? OR c.email LIKE ? OR c.poste LIKE ?
            ORDER BY c.nom, c.prenom ASC
        ";
        $searchTerm = "%{$query}%";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        $data = $stmt->fetchAll();

        return array_map([$this, 'enrichContactData'], $data);
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM contacts WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function deleteByClientId(int $clientId): bool
    {
        $sql = "DELETE FROM contacts WHERE client_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$clientId]);
    }

    public function getStatistiques(): array
    {
        $sql = "
            SELECT 
                statut,
                COUNT(*) as count
            FROM contacts 
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
        $sql = "SELECT COUNT(*) FROM contacts";
        $stmt = $this->pdo->query($sql);
        return (int) $stmt->fetchColumn();
    }

    public function countByClientId(int $clientId): int
    {
        $sql = "SELECT COUNT(*) FROM contacts WHERE client_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$clientId]);
        return (int) $stmt->fetchColumn();
    }

    private function mapToContact(array $data): Contact
    {
        $contact = new Contact(
            clientId: (int) $data['client_id'],
            prenom: $data['prenom'],
            nom: $data['nom'],
            email: $data['email'],
            telephone: $data['telephone'],
            poste: $data['poste'],
            statut: $data['statut'],
            notes: $data['notes'],
            id: (int) $data['id'],
            createdAt: new \DateTime($data['created_at']),
            updatedAt: new \DateTime($data['updated_at'])
        );

        return $contact;
    }

    private function enrichContactData(array $row): array
    {
        $contact = $this->mapToContact($row);
        $array = $contact->toArray();
        $array['client_nom'] = $row['client_nom'] ?? null;
        return $array;
    }
}
