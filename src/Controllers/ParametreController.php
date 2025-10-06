<?php

namespace App\Controllers;

use App\Models\Parametre;
use App\Repositories\ParametreRepository;

class ParametreController extends ApiController
{
    private ParametreRepository $parametreRepository;

    public function __construct(ParametreRepository $parametreRepository)
    {
        $this->parametreRepository = $parametreRepository;
    }

    /**
     * GET /api/parametres - Récupère tous les paramètres
     */
    public function index(): void
    {
        try {
            $parametres = $this->parametreRepository->findAll();
            
            // Organiser les paramètres par catégorie
            $grouped = [
                'jours_travail' => [],
                'horaires' => [],
                'capacite' => [],
                'planification' => [],
                'autres' => []
            ];

            foreach ($parametres as $param) {
                $cle = $param->getCle();
                $data = $param->toArray();

                if (str_starts_with($cle, 'jours_travail_')) {
                    $grouped['jours_travail'][] = $data;
                } elseif (str_starts_with($cle, 'horaire_')) {
                    $grouped['horaires'][] = $data;
                } elseif (str_starts_with($cle, 'heures_travail_')) {
                    $grouped['capacite'][] = $data;
                } elseif (str_contains($cle, 'planification')) {
                    $grouped['planification'][] = $data;
                } else {
                    $grouped['autres'][] = $data;
                }
            }

            $this->jsonResponse([
                'success' => true,
                'data' => [
                    'all' => array_map(fn($p) => $p->toArray(), $parametres),
                    'grouped' => $grouped
                ]
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la récupération des paramètres',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/parametres/:id - Récupère un paramètre par ID
     */
    public function show(int $id): void
    {
        try {
            $parametre = $this->parametreRepository->findById($id);

            if (!$parametre) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Paramètre non trouvé'
                ], 404);
                return;
            }

            $this->jsonResponse([
                'success' => true,
                'data' => $parametre->toArray()
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la récupération du paramètre',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/parametres/cle/:cle - Récupère un paramètre par clé
     */
    public function showByCle(string $cle): void
    {
        try {
            $parametre = $this->parametreRepository->findByCle($cle);

            if (!$parametre) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Paramètre non trouvé'
                ], 404);
                return;
            }

            $this->jsonResponse([
                'success' => true,
                'data' => $parametre->toArray()
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la récupération du paramètre',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/parametres - Crée un nouveau paramètre
     */
    public function store(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            // Validation
            if (empty($data['cle'])) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'La clé est requise'
                ], 400);
                return;
            }

            $parametre = new Parametre(
                cle: $data['cle'],
                valeur: $data['valeur'] ?? null,
                type: $data['type'] ?? 'string',
                description: $data['description'] ?? null
            );

            $createdParametre = $this->parametreRepository->create($parametre);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Paramètre créé avec succès',
                'data' => $createdParametre->toArray()
            ], 201);
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la création du paramètre',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * PUT /api/parametres/:id - Met à jour un paramètre
     */
    public function update(int $id): void
    {
        try {
            $parametre = $this->parametreRepository->findById($id);

            if (!$parametre) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Paramètre non trouvé'
                ], 404);
                return;
            }

            $data = json_decode(file_get_contents('php://input'), true);

            // Mettre à jour les champs
            if (isset($data['cle'])) $parametre->setCle($data['cle']);
            if (isset($data['valeur'])) $parametre->setValeur($data['valeur']);
            if (isset($data['type'])) $parametre->setType($data['type']);
            if (isset($data['description'])) $parametre->setDescription($data['description']);

            $this->parametreRepository->update($parametre);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Paramètre mis à jour avec succès',
                'data' => $parametre->toArray()
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du paramètre',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * PUT /api/parametres/batch - Met à jour plusieurs paramètres en masse
     */
    public function updateBatch(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data) || !is_array($data)) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Données invalides'
                ], 400);
                return;
            }

            $count = $this->parametreRepository->updateMultiple($data);

            $this->jsonResponse([
                'success' => true,
                'message' => "$count paramètre(s) mis à jour avec succès",
                'data' => ['count' => $count]
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour des paramètres',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/parametres/:id - Supprime un paramètre
     */
    public function destroy(int $id): void
    {
        try {
            $parametre = $this->parametreRepository->findById($id);

            if (!$parametre) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Paramètre non trouvé'
                ], 404);
                return;
            }

            $this->parametreRepository->delete($id);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Paramètre supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la suppression du paramètre',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/parametres/:cle/reset - Réinitialise un paramètre à sa valeur par défaut
     */
    public function reset(string $cle): void
    {
        try {
            $success = $this->parametreRepository->resetToDefault($cle);

            if (!$success) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Paramètre non trouvé ou valeur par défaut non disponible'
                ], 404);
                return;
            }

            $this->jsonResponse([
                'success' => true,
                'message' => 'Paramètre réinitialisé avec succès'
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la réinitialisation du paramètre',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

