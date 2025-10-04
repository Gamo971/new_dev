<?php

declare(strict_types=1);

namespace App\Models;

class Contact
{
    private ?int $id;
    private int $clientId;
    private string $prenom;
    private string $nom;
    private ?string $email;
    private ?string $telephone;
    private ?string $poste;
    private string $statut;
    private ?string $notes;
    private \DateTime $createdAt;
    private \DateTime $updatedAt;

    public function __construct(
        int $clientId,
        string $prenom,
        string $nom,
        ?string $email = null,
        ?string $telephone = null,
        ?string $poste = null,
        string $statut = 'actif',
        ?string $notes = null,
        ?int $id = null,
        ?\DateTime $createdAt = null,
        ?\DateTime $updatedAt = null
    ) {
        $this->id = $id;
        $this->clientId = $clientId;
        $this->prenom = $prenom;
        $this->nom = $nom;
        $this->email = $email;
        $this->telephone = $telephone;
        $this->poste = $poste;
        $this->statut = $statut;
        $this->notes = $notes;
        $this->createdAt = $createdAt ?? new \DateTime();
        $this->updatedAt = $updatedAt ?? new \DateTime();
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClientId(): int
    {
        return $this->clientId;
    }

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function getPoste(): ?string
    {
        return $this->poste;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    // Setters
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setClientId(int $clientId): void
    {
        $this->clientId = $clientId;
        $this->updatedAt = new \DateTime();
    }

    public function setPrenom(string $prenom): void
    {
        $this->prenom = $prenom;
        $this->updatedAt = new \DateTime();
    }

    public function setNom(string $nom): void
    {
        $this->nom = $nom;
        $this->updatedAt = new \DateTime();
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
        $this->updatedAt = new \DateTime();
    }

    public function setTelephone(?string $telephone): void
    {
        $this->telephone = $telephone;
        $this->updatedAt = new \DateTime();
    }

    public function setPoste(?string $poste): void
    {
        $this->poste = $poste;
        $this->updatedAt = new \DateTime();
    }

    public function setStatut(string $statut): void
    {
        $this->statut = $statut;
        $this->updatedAt = new \DateTime();
    }

    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
        $this->updatedAt = new \DateTime();
    }

    // Méthodes utilitaires
    public function getStatutLibelle(): string
    {
        return match ($this->statut) {
            'actif' => 'Actif',
            'inactif' => 'Inactif',
            default => 'Inconnu'
        };
    }

    public function getNomComplet(): string
    {
        return trim($this->prenom . ' ' . $this->nom);
    }

    public function isActif(): bool
    {
        return $this->statut === 'actif';
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->clientId,
            'prenom' => $this->prenom,
            'nom' => $this->nom,
            'nom_complet' => $this->getNomComplet(),
            'email' => $this->email,
            'telephone' => $this->telephone,
            'poste' => $this->poste,
            'statut' => $this->statut,
            'statut_libelle' => $this->getStatutLibelle(),
            'notes' => $this->notes,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s')
        ];
    }
}
