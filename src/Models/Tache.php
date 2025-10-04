<?php

declare(strict_types=1);

namespace App\Models;

class Tache
{
    private ?int $id;
    private int $missionId;
    private string $nom;
    private ?string $description;
    private string $statut;
    private string $priorite;
    private ?\DateTime $dateEcheance;
    private ?\DateTime $dateFinReelle;
    private int $tempsEstime;
    private int $tempsReel;
    private int $ordre;
    private ?string $assigneA;
    private ?string $notes;
    private \DateTime $createdAt;
    private \DateTime $updatedAt;

    public function __construct(
        int $missionId,
        string $nom,
        ?string $description = null,
        string $statut = 'a_faire',
        string $priorite = 'normale',
        ?\DateTime $dateEcheance = null,
        ?\DateTime $dateFinReelle = null,
        int $tempsEstime = 0,
        int $tempsReel = 0,
        int $ordre = 0,
        ?string $assigneA = null,
        ?string $notes = null,
        ?int $id = null,
        ?\DateTime $createdAt = null,
        ?\DateTime $updatedAt = null
    ) {
        $this->id = $id;
        $this->missionId = $missionId;
        $this->nom = $nom;
        $this->description = $description;
        $this->statut = $statut;
        $this->priorite = $priorite;
        $this->dateEcheance = $dateEcheance;
        $this->dateFinReelle = $dateFinReelle;
        $this->tempsEstime = $tempsEstime;
        $this->tempsReel = $tempsReel;
        $this->ordre = $ordre;
        $this->assigneA = $assigneA;
        $this->notes = $notes;
        $this->createdAt = $createdAt ?? new \DateTime();
        $this->updatedAt = $updatedAt ?? new \DateTime();
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMissionId(): int
    {
        return $this->missionId;
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

    public function getDateEcheance(): ?\DateTime
    {
        return $this->dateEcheance;
    }

    public function getDateFinReelle(): ?\DateTime
    {
        return $this->dateFinReelle;
    }

    public function getTempsEstime(): int
    {
        return $this->tempsEstime;
    }

    public function getTempsReel(): int
    {
        return $this->tempsReel;
    }

    public function getOrdre(): int
    {
        return $this->ordre;
    }

    public function getAssigneA(): ?string
    {
        return $this->assigneA;
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

    public function setMissionId(int $missionId): void
    {
        $this->missionId = $missionId;
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

    public function setDateEcheance(?\DateTime $dateEcheance): void
    {
        $this->dateEcheance = $dateEcheance;
        $this->updatedAt = new \DateTime();
    }

    public function setDateFinReelle(?\DateTime $dateFinReelle): void
    {
        $this->dateFinReelle = $dateFinReelle;
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

    public function setOrdre(int $ordre): void
    {
        $this->ordre = $ordre;
        $this->updatedAt = new \DateTime();
    }

    public function setAssigneA(?string $assigneA): void
    {
        $this->assigneA = $assigneA;
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
            'a_faire' => 'À faire',
            'en_cours' => 'En cours',
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

    public function getProgression(): float
    {
        if ($this->tempsEstime <= 0) {
            return 0.0;
        }
        return min(100.0, ($this->tempsReel / $this->tempsEstime) * 100);
    }

    public function isEnRetard(): bool
    {
        if (!$this->dateEcheance || $this->statut === 'terminee') {
            return false;
        }
        return new \DateTime() > $this->dateEcheance;
    }

    public function isTerminee(): bool
    {
        return $this->statut === 'terminee';
    }

    public function isEnCours(): bool
    {
        return $this->statut === 'en_cours';
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'mission_id' => $this->missionId,
            'nom' => $this->nom,
            'description' => $this->description,
            'statut' => $this->statut,
            'statut_libelle' => $this->getStatutLibelle(),
            'priorite' => $this->priorite,
            'priorite_libelle' => $this->getPrioriteLibelle(),
            'priorite_couleur' => $this->getPrioriteCouleur(),
            'date_echeance' => $this->dateEcheance?->format('Y-m-d'),
            'date_fin_reelle' => $this->dateFinReelle?->format('Y-m-d H:i:s'),
            'temps_estime' => $this->tempsEstime,
            'temps_reel' => $this->tempsReel,
            'temps_estime_formate' => $this->getTempsEstimeFormate(),
            'temps_reel_formate' => $this->getTempsReelFormate(),
            'ordre' => $this->ordre,
            'assigne_a' => $this->assigneA,
            'progression' => round($this->getProgression(), 1),
            'en_retard' => $this->isEnRetard(),
            'terminee' => $this->isTerminee(),
            'en_cours' => $this->isEnCours(),
            'notes' => $this->notes,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s')
        ];
    }
}
