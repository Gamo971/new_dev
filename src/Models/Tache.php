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
    private ?\DateTime $dateEcheance;
    private ?\DateTime $datePlanifiee;
    private ?string $heureDebutPlanifiee;
    private string $planificationType;
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
        ?\DateTime $dateEcheance = null,
        ?\DateTime $datePlanifiee = null,
        ?string $heureDebutPlanifiee = null,
        string $planificationType = 'automatique',
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
        $this->dateEcheance = $dateEcheance;
        $this->datePlanifiee = $datePlanifiee;
        $this->heureDebutPlanifiee = $heureDebutPlanifiee;
        $this->planificationType = $planificationType;
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


    public function getDateEcheance(): ?\DateTime
    {
        return $this->dateEcheance;
    }

    public function getDatePlanifiee(): ?\DateTime
    {
        return $this->datePlanifiee;
    }

    public function getHeureDebutPlanifiee(): ?string
    {
        return $this->heureDebutPlanifiee;
    }

    public function getPlanificationType(): string
    {
        return $this->planificationType;
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


    public function setDateEcheance(?\DateTime $dateEcheance): void
    {
        $this->dateEcheance = $dateEcheance;
        $this->updatedAt = new \DateTime();
    }

    public function setDatePlanifiee(?\DateTime $datePlanifiee): void
    {
        $this->datePlanifiee = $datePlanifiee;
        $this->updatedAt = new \DateTime();
    }

    public function setHeureDebutPlanifiee(?string $heureDebutPlanifiee): void
    {
        $this->heureDebutPlanifiee = $heureDebutPlanifiee;
        $this->updatedAt = new \DateTime();
    }

    public function setPlanificationType(string $planificationType): void
    {
        $this->planificationType = $planificationType;
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

    public function isPlanifiee(): bool
    {
        return $this->datePlanifiee !== null;
    }

    public function isPlanificationManuelle(): bool
    {
        return $this->planificationType === 'manuelle';
    }

    public function isPlanificationAutomatique(): bool
    {
        return $this->planificationType === 'automatique';
    }

    public function getPlanificationTypeLibelle(): string
    {
        return match ($this->planificationType) {
            'manuelle' => 'Manuelle',
            'automatique' => 'Automatique',
            default => 'Inconnue'
        };
    }

    public function isEnRetardPlanification(): bool
    {
        if (!$this->datePlanifiee || $this->statut === 'terminee') {
            return false;
        }
        return new \DateTime() > $this->datePlanifiee && $this->statut !== 'terminee';
    }

    public function getMargeAvantEcheance(): ?int
    {
        if (!$this->dateEcheance || !$this->datePlanifiee) {
            return null;
        }
        $diff = $this->dateEcheance->diff($this->datePlanifiee);
        return (int) $diff->format('%r%a'); // Nombre de jours (négatif si planifiée après échéance)
    }

    public function getStatutPlanification(): string
    {
        if (!$this->datePlanifiee) {
            return 'non_planifiee';
        }
        
        if ($this->isTerminee()) {
            return 'terminee';
        }
        
        $now = new \DateTime();
        $today = new \DateTime($now->format('Y-m-d'));
        $planDate = new \DateTime($this->datePlanifiee->format('Y-m-d'));
        
        if ($planDate < $today) {
            return 'en_retard';
        } elseif ($planDate == $today) {
            return 'aujourdhui';
        } else {
            return 'a_venir';
        }
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
            'date_echeance' => $this->dateEcheance?->format('Y-m-d'),
            'date_planifiee' => $this->datePlanifiee?->format('Y-m-d'),
            'heure_debut_planifiee' => $this->heureDebutPlanifiee,
            'planification_type' => $this->planificationType,
            'planification_type_libelle' => $this->getPlanificationTypeLibelle(),
            'planification_manuelle' => $this->isPlanificationManuelle(),
            'planification_automatique' => $this->isPlanificationAutomatique(),
            'date_fin_reelle' => $this->dateFinReelle?->format('Y-m-d H:i:s'),
            'temps_estime' => $this->tempsEstime,
            'temps_reel' => $this->tempsReel,
            'temps_estime_formate' => $this->getTempsEstimeFormate(),
            'temps_reel_formate' => $this->getTempsReelFormate(),
            'ordre' => $this->ordre,
            'assigne_a' => $this->assigneA,
            'progression' => round($this->getProgression(), 1),
            'en_retard' => $this->isEnRetard(),
            'en_retard_planification' => $this->isEnRetardPlanification(),
            'statut_planification' => $this->getStatutPlanification(),
            'marge_avant_echeance' => $this->getMargeAvantEcheance(),
            'terminee' => $this->isTerminee(),
            'en_cours' => $this->isEnCours(),
            'planifiee' => $this->isPlanifiee(),
            'notes' => $this->notes,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s')
        ];
    }
}
