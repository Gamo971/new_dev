<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\TacheRepository;
use App\Models\Tache;

class TacheController extends ApiController
{
    private TacheRepository $tacheRepository;

    public function __construct(TacheRepository $tacheRepository)
    {
        $this->tacheRepository = $tacheRepository;
    }

    public function index(): void
    {
        try {
            $data = $this->tacheRepository->findAll();
            
            $this->sendSuccess($data, 'Tâches récupérées avec succès');
        } catch (\Exception $e) {
            $this->sendError('Erreur lors de la récupération des tâches: ' . $e->getMessage(), 500);
        }
    }

    public function show(int $id): void
    {
        try {
            $tache = $this->tacheRepository->findById($id);
            
            if (!$tache) {
                $this->sendError('Tâche non trouvée', 404);
                return;
            }
            
            $this->sendSuccess($tache->toArray(), 'Tâche récupérée avec succès');
        } catch (\Exception $e) {
            $this->sendError('Erreur lors de la récupération de la tâche: ' . $e->getMessage(), 500);
        }
    }

    public function store(): void
    {
        try {
            $data = $this->getRequestData();
            
            // Validation des champs requis
            $this->validateRequired($data, ['mission_id', 'nom']);
            
            // Validation de la date d'échéance si fournie
            if (isset($data['date_echeance']) && !$this->validateDate($data['date_echeance'])) {
                $this->sendError('Format de date d\'échéance invalide (Y-m-d)');
                return;
            }
            
            // Création de la tâche
            $tache = new Tache(
                missionId: (int) $data['mission_id'],
                nom: $this->sanitizeString($data['nom']),
                description: $this->sanitizeString($data['description'] ?? null),
                statut: $this->sanitizeString($data['statut'] ?? 'a_faire'),
                priorite: $this->sanitizeString($data['priorite'] ?? 'normale'),
                dateEcheance: isset($data['date_echeance']) ? new \DateTime($data['date_echeance']) : null,
                tempsEstime: $this->sanitizeInt($data['temps_estime'] ?? 0) ?? 0,
                ordre: $this->sanitizeInt($data['ordre'] ?? 0) ?? 0,
                assigneA: $this->sanitizeString($data['assigne_a'] ?? null),
                notes: $this->sanitizeString($data['notes'] ?? null)
            );
            
            $savedTache = $this->tacheRepository->save($tache);
            
            $this->sendSuccess($savedTache->toArray(), 'Tâche créée avec succès', 201);
        } catch (\Exception $e) {
            $this->sendError('Erreur lors de la création de la tâche: ' . $e->getMessage(), 500);
        }
    }

    public function update(int $id): void
    {
        try {
            $tache = $this->tacheRepository->findById($id);
            
            if (!$tache) {
                $this->sendError('Tâche non trouvée', 404);
                return;
            }
            
            $data = $this->getRequestData();
            
            // Validation de la date d'échéance si fournie
            if (isset($data['date_echeance']) && !$this->validateDate($data['date_echeance'])) {
                $this->sendError('Format de date d\'échéance invalide (Y-m-d)');
                return;
            }
            
            // Mise à jour des champs
            if (isset($data['mission_id'])) {
                $tache->setMissionId((int) $data['mission_id']);
            }
            if (isset($data['nom'])) {
                $tache->setNom($this->sanitizeString($data['nom']));
            }
            if (isset($data['description'])) {
                $tache->setDescription($this->sanitizeString($data['description']));
            }
            if (isset($data['statut'])) {
                $tache->setStatut($this->sanitizeString($data['statut']));
            }
            if (isset($data['priorite'])) {
                $tache->setPriorite($this->sanitizeString($data['priorite']));
            }
            if (isset($data['date_echeance'])) {
                $tache->setDateEcheance(new \DateTime($data['date_echeance']));
            }
            if (isset($data['temps_estime'])) {
                $tache->setTempsEstime($this->sanitizeInt($data['temps_estime']) ?? 0);
            }
            if (isset($data['temps_reel'])) {
                $tache->setTempsReel($this->sanitizeInt($data['temps_reel']) ?? 0);
            }
            if (isset($data['ordre'])) {
                $tache->setOrdre($this->sanitizeInt($data['ordre']) ?? 0);
            }
            if (isset($data['assigne_a'])) {
                $tache->setAssigneA($this->sanitizeString($data['assigne_a']));
            }
            if (isset($data['notes'])) {
                $tache->setNotes($this->sanitizeString($data['notes']));
            }
            
            $updatedTache = $this->tacheRepository->save($tache);
            
            $this->sendSuccess($updatedTache->toArray(), 'Tâche mise à jour avec succès');
        } catch (\Exception $e) {
            $this->sendError('Erreur lors de la mise à jour de la tâche: ' . $e->getMessage(), 500);
        }
    }

    public function delete(int $id): void
    {
        try {
            $tache = $this->tacheRepository->findById($id);
            
            if (!$tache) {
                $this->sendError('Tâche non trouvée', 404);
                return;
            }
            
            $success = $this->tacheRepository->delete($id);
            
            if ($success) {
                $this->sendSuccess([], 'Tâche supprimée avec succès');
            } else {
                $this->sendError('Erreur lors de la suppression de la tâche');
            }
        } catch (\Exception $e) {
            $this->sendError('Erreur lors de la suppression de la tâche: ' . $e->getMessage(), 500);
        }
    }

    public function findByMission(int $missionId): void
    {
        try {
            $taches = $this->tacheRepository->findByMissionId($missionId);
            $data = array_map(fn(Tache $tache) => $tache->toArray(), $taches);
            
            $this->sendSuccess($data, 'Tâches de la mission récupérées avec succès');
        } catch (\Exception $e) {
            $this->sendError('Erreur lors de la récupération des tâches: ' . $e->getMessage(), 500);
        }
    }

    public function findByStatut(string $statut): void
    {
        try {
            $taches = $this->tacheRepository->findByStatut($statut);
            $data = array_map(fn(Tache $tache) => $tache->toArray(), $taches);
            
            $this->sendSuccess($data, 'Tâches récupérées avec succès');
        } catch (\Exception $e) {
            $this->sendError('Erreur lors de la récupération des tâches: ' . $e->getMessage(), 500);
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
            
            $taches = $this->tacheRepository->search($query);
            $data = array_map(fn(Tache $tache) => $tache->toArray(), $taches);
            
            $this->sendSuccess($data, 'Recherche effectuée avec succès');
        } catch (\Exception $e) {
            $this->sendError('Erreur lors de la recherche: ' . $e->getMessage(), 500);
        }
    }

    public function enRetard(): void
    {
        try {
            $taches = $this->tacheRepository->findEnRetard();
            $data = array_map(fn(Tache $tache) => $tache->toArray(), $taches);
            
            $this->sendSuccess($data, 'Tâches en retard récupérées avec succès');
        } catch (\Exception $e) {
            $this->sendError('Erreur lors de la récupération des tâches en retard: ' . $e->getMessage(), 500);
        }
    }

    public function findByAssigne(string $assigneA): void
    {
        try {
            $taches = $this->tacheRepository->findByAssigne($assigneA);
            $data = array_map(fn(Tache $tache) => $tache->toArray(), $taches);
            
            $this->sendSuccess($data, 'Tâches de l\'assigné récupérées avec succès');
        } catch (\Exception $e) {
            $this->sendError('Erreur lors de la récupération des tâches: ' . $e->getMessage(), 500);
        }
    }

    public function statistiques(): void
    {
        try {
            $stats = $this->tacheRepository->getStatistiques();
            $globales = $this->tacheRepository->getStatistiquesGlobales();
            $parMission = $this->tacheRepository->getStatistiquesParMission();
            
            $data = [
                'globales' => $globales,
                'par_statut' => $stats,
                'par_mission' => $parMission
            ];
            
            $this->sendSuccess($data, 'Statistiques récupérées avec succès');
        } catch (\Exception $e) {
            $this->sendError('Erreur lors de la récupération des statistiques: ' . $e->getMessage(), 500);
        }
    }
}
