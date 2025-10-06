<?php

namespace App\Models;

class Parametre
{
    private ?int $id;
    private string $cle;
    private ?string $valeur;
    private string $type;
    private ?string $description;
    private \DateTime $createdAt;
    private \DateTime $updatedAt;

    public function __construct(
        string $cle,
        ?string $valeur = null,
        string $type = 'string',
        ?string $description = null,
        ?int $id = null,
        ?\DateTime $createdAt = null,
        ?\DateTime $updatedAt = null
    ) {
        $this->id = $id;
        $this->cle = $cle;
        $this->valeur = $valeur;
        $this->type = $type;
        $this->description = $description;
        $this->createdAt = $createdAt ?? new \DateTime();
        $this->updatedAt = $updatedAt ?? new \DateTime();
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getCle(): string { return $this->cle; }
    public function getValeur(): ?string { return $this->valeur; }
    public function getType(): string { return $this->type; }
    public function getDescription(): ?string { return $this->description; }
    public function getCreatedAt(): \DateTime { return $this->createdAt; }
    public function getUpdatedAt(): \DateTime { return $this->updatedAt; }

    // Setters
    public function setId(?int $id): void { $this->id = $id; }
    public function setCle(string $cle): void { $this->cle = $cle; $this->updatedAt = new \DateTime(); }
    public function setValeur(?string $valeur): void { $this->valeur = $valeur; $this->updatedAt = new \DateTime(); }
    public function setType(string $type): void { $this->type = $type; $this->updatedAt = new \DateTime(); }
    public function setDescription(?string $description): void { $this->description = $description; $this->updatedAt = new \DateTime(); }

    /**
     * Retourne la valeur typée selon le type du paramètre
     * @return mixed
     */
    public function getValeurTypee()
    {
        if ($this->valeur === null) {
            return null;
        }

        return match ($this->type) {
            'boolean' => (bool)$this->valeur,
            'number' => is_numeric($this->valeur) ? (float)$this->valeur : 0,
            'integer' => (int)$this->valeur,
            'time' => $this->valeur,
            default => $this->valeur,
        };
    }

    /**
     * Retourne true si c'est un jour de travail
     * @param string $jour - lundi, mardi, etc.
     * @return bool
     */
    public static function isJourTravail(string $jour, array $parametres): bool
    {
        $cle = 'jours_travail_' . strtolower($jour);
        foreach ($parametres as $param) {
            if ($param->getCle() === $cle) {
                return (bool)$param->getValeurTypee();
            }
        }
        return true; // Par défaut, on considère que c'est un jour travaillé
    }

    /**
     * Retourne le nombre d'heures de travail par jour
     * @param array $parametres
     * @return float
     */
    public static function getHeuresTravailParJour(array $parametres): float
    {
        foreach ($parametres as $param) {
            if ($param->getCle() === 'heures_travail_par_jour') {
                return (float)$param->getValeurTypee();
            }
        }
        return 7.0; // Par défaut
    }

    /**
     * Retourne le buffer de planification (marge de sécurité)
     * @param array $parametres
     * @return float
     */
    public static function getBufferPlanification(array $parametres): float
    {
        foreach ($parametres as $param) {
            if ($param->getCle() === 'buffer_planification') {
                return (float)$param->getValeurTypee();
            }
        }
        return 0.2; // 20% par défaut
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'cle' => $this->cle,
            'valeur' => $this->valeur,
            'valeur_typee' => $this->getValeurTypee(),
            'type' => $this->type,
            'description' => $this->description,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}

