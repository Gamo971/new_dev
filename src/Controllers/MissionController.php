<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\MissionRepository;
use App\Models\Mission;

class MissionController extends ApiController
{
    private MissionRepository $missionRepository;

    public function __construct(MissionRepository $missionRepository)
    {
        $this->missionRepository = $missionRepository;
    }

    public function index(): void
    {
        try {
            $data = $this->missionRepository->findAll();
            
            $this->sendSuccess($data, 'Missions récupérées avec succès');
        } catch (\Exception $e) {
            $this->sendError('Erreur lors de la récupération des missions: ' . $e->getMessage(), 500);
        }
    }

    public function show(int $id): void
    {
        try {
            $mission = $this->missionRepository->findById($id);
            
            if (!$mission) {
                $this->sendError('Mission non trouvée', 404);
                return;
            }
            
            $this->sendSuccess($mission->toArray(), 'Mission récupérée avec succès');
        } catch (\Exception $e) {
            $this->sendError('Erreur lors de la récupération de la mission: ' . $e->getMessage(), 500);
        }
    }

    public function store(): void
    {
        try {
            $data = $this->getRequestData();
            
            // Validation des champs requis
            $this->validateRequired($data, ['client_id', 'nom']);
            
            // Validation des dates si fournies
            if (isset($data['date_debut']) && !$this->validateDate($data['date_debut'])) {
                $this->sendError('Format de date de début invalide (Y-m-d)');
                return;
            }
            if (isset($data['date_fin_prevue']) && !$this->validateDate($data['date_fin_prevue'])) {
                $this->sendError('Format de date de fin prévue invalide (Y-m-d)');
                return;
            }
            
            // Création de la mission
            $mission = new Mission(
                clientId: (int) $data['client_id'],
                nom: $this->sanitizeString($data['nom']),
                description: $this->sanitizeString($data['description'] ?? null),
                statut: $this->sanitizeString($data['statut'] ?? 'en_attente'),
                priorite: $this->sanitizeString($data['priorite'] ?? 'normale'),
                dateDebut: isset($data['date_debut']) ? new \DateTime($data['date_debut']) : null,
                dateFinPrevue: isset($data['date_fin_prevue']) ? new \DateTime($data['date_fin_prevue']) : null,
                budgetPrevu: $this->sanitizeFloat($data['budget_prevu'] ?? null),
                tempsEstime: $this->sanitizeInt($data['temps_estime'] ?? 0) ?? 0,
                notes: $this->sanitizeString($data['notes'] ?? null)
            );
            
            $savedMission = $this->missionRepository->save($mission);
            
            $this->sendSuccess($savedMission->toArray(), 'Mission créée avec succès', 201);
        } catch (\Exception $e) {
            $this->sendError('Erreur lors de la création de la mission: ' . $e->getMessage(), 500);
        }
    }

    public function update(int $id): void
    {
        try {
            $mission = $this->missionRepository->findById($id);
            
            if (!$mission) {
                $this->sendError('Mission non trouvée', 404);
                return;
            }
            
            $data = $this->getRequestData();
            
            // Validation des dates si fournies
            if (isset($data['date_debut']) && !$this->validateDate($data['date_debut'])) {
                $this->sendError('Format de date de début invalide (Y-m-d)');
                return;
            }
            if (isset($data['date_fin_prevue']) && !$this->validateDate($data['date_fin_prevue'])) {
                $this->sendError('Format de date de fin prévue invalide (Y-m-d)');
                return;
            }
            
            // Mise à jour des champs
            if (isset($data['client_id'])) {
                $mission->setClientId((int) $data['client_id']);
            }
            if (isset($data['nom'])) {
                $mission->setNom($this->sanitizeString($data['nom']));
            }
            if (isset($data['description'])) {
                $mission->setDescription($this->sanitizeString($data['description']));
            }
            if (isset($data['statut'])) {
                $mission->setStatut($this->sanitizeString($data['statut']));
            }
            if (isset($data['priorite'])) {
                $mission->setPriorite($this->sanitizeString($data['priorite']));
            }
            if (isset($data['date_debut'])) {
                $mission->setDateDebut(new \DateTime($data['date_debut']));
            }
            if (isset($data['date_fin_prevue'])) {
                $mission->setDateFinPrevue(new \DateTime($data['date_fin_prevue']));
            }
            if (isset($data['budget_prevu'])) {
                $mission->setBudgetPrevu($this->sanitizeFloat($data['budget_prevu']));
            }
            if (isset($data['budget_reel'])) {
                $mission->setBudgetReel($this->sanitizeFloat($data['budget_reel']));
            }
            // Le temps_estime n'est plus modifiable manuellement, il est calculé depuis les tâches
            // if (isset($data['temps_estime'])) {
            //     $mission->setTempsEstime($this->sanitizeInt($data['temps_estime']) ?? 0);
            // }
            if (isset($data['temps_reel'])) {
                $mission->setTempsReel($this->sanitizeInt($data['temps_reel']) ?? 0);
            }
            if (isset($data['notes'])) {
                $mission->setNotes($this->sanitizeString($data['notes']));
            }
            
            $updatedMission = $this->missionRepository->save($mission);
            
            $this->sendSuccess($updatedMission->toArray(), 'Mission mise à jour avec succès');
        } catch (\Exception $e) {
            $this->sendError('Erreur lors de la mise à jour de la mission: ' . $e->getMessage(), 500);
        }
    }

    public function delete(int $id): void
    {
        try {
            $mission = $this->missionRepository->findById($id);
            
            if (!$mission) {
                $this->sendError('Mission non trouvée', 404);
                return;
            }
            
            $success = $this->missionRepository->delete($id);
            
            if ($success) {
                $this->sendSuccess([], 'Mission supprimée avec succès');
            } else {
                $this->sendError('Erreur lors de la suppression de la mission');
            }
        } catch (\Exception $e) {
            $this->sendError('Erreur lors de la suppression de la mission: ' . $e->getMessage(), 500);
        }
    }

    public function findByClient(int $clientId): void
    {
        try {
            $missions = $this->missionRepository->findByClientId($clientId);
            $data = array_map(fn(Mission $mission) => $mission->toArray(), $missions);
            
            $this->sendSuccess($data, 'Missions du client récupérées avec succès');
        } catch (\Exception $e) {
            $this->sendError('Erreur lors de la récupération des missions: ' . $e->getMessage(), 500);
        }
    }

    public function findByStatut(string $statut): void
    {
        try {
            $missions = $this->missionRepository->findByStatut($statut);
            $data = array_map(fn(Mission $mission) => $mission->toArray(), $missions);
            
            $this->sendSuccess($data, 'Missions récupérées avec succès');
        } catch (\Exception $e) {
            $this->sendError('Erreur lors de la récupération des missions: ' . $e->getMessage(), 500);
        }
    }

    public function search(): void
    {
        try {
            $query = $this->getQueryParam('q', '');
            
            if (empty($query)) {
                $this->sendError('Le paramètre de recherche est requis');
                return;
            }
            
            $missions = $this->missionRepository->search($query);
            $data = array_map(fn(Mission $mission) => $mission->toArray(), $missions);
            
            $this->sendSuccess($data, 'Recherche effectuée avec succès');
        } catch (\Exception $e) {
            $this->sendError('Erreur lors de la recherche: ' . $e->getMessage(), 500);
        }
    }

    public function enRetard(): void
    {
        try {
            $missions = $this->missionRepository->findEnRetard();
            $data = array_map(fn(Mission $mission) => $mission->toArray(), $missions);
            
            $this->sendSuccess($data, 'Missions en retard récupérées avec succès');
        } catch (\Exception $e) {
            $this->sendError('Erreur lors de la récupération des missions en retard: ' . $e->getMessage(), 500);
        }
    }

    public function statistiques(): void
    {
        try {
            $stats = $this->missionRepository->getStatistiques();
            $globales = $this->missionRepository->getStatistiquesGlobales();
            
            $data = [
                'globales' => $globales,
                'par_statut' => $stats
            ];
            
            $this->sendSuccess($data, 'Statistiques récupérées avec succès');
        } catch (\Exception $e) {
            $this->sendError('Erreur lors de la récupération des statistiques: ' . $e->getMessage(), 500);
        }
    }
}
