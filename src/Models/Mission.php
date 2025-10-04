<?php

declare(strict_types=1);

namespace App\Models;

class Mission
{
    private ?int $id;
    private int $clientId;
    private string $nom;
    private ?string $description;
    private string $statut;
    private string $priorite;
    private ?\DateTime $dateDebut;
    private ?\DateTime $dateFinPrevue;
    private ?\DateTime $dateFinReelle;
    private ?float $budgetPrevu;
    private ?float $budgetReel;
    private int $tempsEstime;
    private int $tempsReel;
    private ?string $notes;
    private \DateTime $createdAt;
    private \DateTime $updatedAt;

    public function __construct(
        int $clientId,
        string $nom,
        ?string $description = null,
        string $statut = 'en_attente',
        string $priorite = 'normale',
        ?\DateTime $dateDebut = null,
        ?\DateTime $dateFinPrevue = null,
        ?\DateTime $dateFinReelle = null,
        ?float $budgetPrevu = null,
        ?float $budgetReel = null,
        int $tempsEstime = 0,
        int $tempsReel = 0,
        ?string $notes = null,
        ?int $id = null,
        ?\DateTime $createdAt = null,
        ?\DateTime $updatedAt = null
    ) {
        $this->id = $id;
        $this->clientId = $clientId;
        $this->nom = $nom;
        $this->description = $description;
        $this->statut = $statut;
        $this->priorite = $priorite;
        $this->dateDebut = $dateDebut;
        $this->dateFinPrevue = $dateFinPrevue;
        $this->dateFinReelle = $dateFinReelle;
        $this->budgetPrevu = $budgetPrevu;
        $this->budgetReel = $budgetReel;
        $this->tempsEstime = $tempsEstime;
        $this->tempsReel = $tempsReel;
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

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function getPriorite(): string
    {
        return $this->priorite;
    }

    public function getDateDebut(): ?\DateTime
    {
        return $this->dateDebut;
    }

    public function getDateFinPrevue(): ?\DateTime
    {
        return $this->dateFinPrevue;
    }

    public function getDateFinReelle(): ?\DateTime
    {
        return $this->dateFinReelle;
    }

    public function getBudgetPrevu(): ?float
    {
        return $this->budgetPrevu;
    }

    public function getBudgetReel(): ?float
    {
        return $this->budgetReel;
    }

    public function getTempsEstime(): int
    {
        return $this->tempsEstime;
    }

    public function getTempsReel(): int
    {
        return $this->tempsReel;
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

    public function setNom(string $nom): void
    {
        $this->nom = $nom;
        $this->updatedAt = new \DateTime();
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
        $this->updatedAt = new \DateTime();
    }

    public function setStatut(string $statut): void
    {
        $this->statut = $statut;
        $this->updatedAt = new \DateTime();
    }

    public function setPriorite(string $priorite): void
    {
        $this->priorite = $priorite;
        $this->updatedAt = new \DateTime();
    }

    public function setDateDebut(?\DateTime $dateDebut): void
    {
        $this->dateDebut = $dateDebut;
        $this->updatedAt = new \DateTime();
    }

    public function setDateFinPrevue(?\DateTime $dateFinPrevue): void
    {
        $this->dateFinPrevue = $dateFinPrevue;
        $this->updatedAt = new \DateTime();
    }

    public function setDateFinReelle(?\DateTime $dateFinReelle): void
    {
        $this->dateFinReelle = $dateFinReelle;
        $this->updatedAt = new \DateTime();
    }

    public function setBudgetPrevu(?float $budgetPrevu): void
    {
        $this->budgetPrevu = $budgetPrevu;
        $this->updatedAt = new \DateTime();
    }

    public function setBudgetReel(?float $budgetReel): void
    {
        $this->budgetReel = $budgetReel;
        $this->updatedAt = new \DateTime();
    }

    public function setTempsEstime(int $tempsEstime): void
    {
        $this->tempsEstime = $tempsEstime;
        $this->updatedAt = new \DateTime();
    }

    public function setTempsReel(int $tempsReel): void
    {
        $this->tempsReel = $tempsReel;
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
            'en_attente' => 'En attente',
            'en_cours' => 'En cours',
            'en_pause' => 'En pause',
            'terminee' => 'Terminée',
            'annulee' => 'Annulée',
            default => 'Inconnu'
        };
    }

    public function getPrioriteLibelle(): string
    {
        return match ($this->priorite) {
            'basse' => 'Basse',
            'normale' => 'Normale',
            'haute' => 'Haute',
            'urgente' => 'Urgente',
            default => 'Inconnue'
        };
    }

    public function getPrioriteCouleur(): string
    {
        return match ($this->priorite) {
            'basse' => 'bg-green-100 text-green-800',
            'normale' => 'bg-blue-100 text-blue-800',
            'haute' => 'bg-yellow-100 text-yellow-800',
            'urgente' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getTempsEstimeFormate(): string
    {
        $heures = intval($this->tempsEstime / 60);
        $minutes = $this->tempsEstime % 60;
        
        if ($heures > 0) {
            return $minutes > 0 ? "{$heures}h {$minutes}min" : "{$heures}h";
        }
        return "{$minutes}min";
    }

    public function getTempsReelFormate(): string
    {
        $heures = intval($this->tempsReel / 60);
        $minutes = $this->tempsReel % 60;
        
        if ($heures > 0) {
            return $minutes > 0 ? "{$heures}h {$minutes}min" : "{$heures}h";
        }
        return "{$minutes}min";
    }

    public function getDuree(): ?int
    {
        if ($this->dateDebut && $this->dateFinPrevue) {
            return $this->dateFinPrevue->diff($this->dateDebut)->days;
        }
        return null;
    }

    public function getProgression(): float
    {
        if ($this->tempsEstime <= 0) {
            return 0.0;
        }
        return min(100.0, ($this->tempsReel / $this->tempsEstime) * 100);
    }

    public function isEnRetard(): bool
    {
        if (!$this->dateFinPrevue || $this->statut === 'terminee') {
            return false;
        }
        return new \DateTime() > $this->dateFinPrevue;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->clientId,
            'nom' => $this->nom,
            'description' => $this->description,
            'statut' => $this->statut,
            'statut_libelle' => $this->getStatutLibelle(),
            'priorite' => $this->priorite,
            'priorite_libelle' => $this->getPrioriteLibelle(),
            'priorite_couleur' => $this->getPrioriteCouleur(),
            'date_debut' => $this->dateDebut?->format('Y-m-d'),
            'date_fin_prevue' => $this->dateFinPrevue?->format('Y-m-d'),
            'date_fin_reelle' => $this->dateFinReelle?->format('Y-m-d H:i:s'),
            'budget_prevu' => $this->budgetPrevu,
            'budget_reel' => $this->budgetReel,
            'temps_estime' => $this->tempsEstime,
            'temps_reel' => $this->tempsReel,
            'temps_estime_formate' => $this->getTempsEstimeFormate(),
            'temps_reel_formate' => $this->getTempsReelFormate(),
            'duree' => $this->getDuree(),
            'progression' => round($this->getProgression(), 1),
            'en_retard' => $this->isEnRetard(),
            'notes' => $this->notes,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s')
        ];
    }
}
