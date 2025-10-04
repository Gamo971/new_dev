<?php

declare(strict_types=1);

namespace App\Models;

class Client
{
    private ?int $id;
    private string $nom;
    private ?string $email;
    private ?string $telephone;
    private ?string $adresse;
    private ?string $ville;
    private ?string $codePostal;
    private string $pays;
    private ?string $siret;
    private string $statut;
    private ?string $notes;
    private \DateTime $createdAt;
    private \DateTime $updatedAt;

    public function __construct(
        string $nom,
        ?string $email = null,
        ?string $telephone = null,
        ?string $adresse = null,
        ?string $ville = null,
        ?string $codePostal = null,
        string $pays = 'France',
        ?string $siret = null,
        string $statut = 'actif',
        ?string $notes = null,
        ?int $id = null,
        ?\DateTime $createdAt = null,
        ?\DateTime $updatedAt = null
    ) {
        $this->id = $id;
        $this->nom = $nom;
        $this->email = $email;
        $this->telephone = $telephone;
        $this->adresse = $adresse;
        $this->ville = $ville;
        $this->codePostal = $codePostal;
        $this->pays = $pays;
        $this->siret = $siret;
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

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function getPays(): string
    {
        return $this->pays;
    }

    public function getSiret(): ?string
    {
        return $this->siret;
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

    public function setAdresse(?string $adresse): void
    {
        $this->adresse = $adresse;
        $this->updatedAt = new \DateTime();
    }

    public function setVille(?string $ville): void
    {
        $this->ville = $ville;
        $this->updatedAt = new \DateTime();
    }

    public function setCodePostal(?string $codePostal): void
    {
        $this->codePostal = $codePostal;
        $this->updatedAt = new \DateTime();
    }

    public function setPays(string $pays): void
    {
        $this->pays = $pays;
        $this->updatedAt = new \DateTime();
    }

    public function setSiret(?string $siret): void
    {
        $this->siret = $siret;
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
            'suspendu' => 'Suspendu',
            default => 'Inconnu'
        };
    }

    public function getAdresseComplete(): string
    {
        $parts = array_filter([
            $this->adresse,
            $this->codePostal ? $this->codePostal . ' ' . $this->ville : $this->ville,
            $this->pays
        ]);
        return implode(', ', $parts);
    }

    public function getNomComplet(): string
    {
        return $this->nom;
    }

    public function isActif(): bool
    {
        return $this->statut === 'actif';
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'email' => $this->email,
            'telephone' => $this->telephone,
            'adresse' => $this->adresse,
            'ville' => $this->ville,
            'code_postal' => $this->codePostal,
            'pays' => $this->pays,
            'siret' => $this->siret,
            'statut' => $this->statut,
            'statut_libelle' => $this->getStatutLibelle(),
            'notes' => $this->notes,
            'adresse_complete' => $this->getAdresseComplete(),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s')
        ];
    }
}
