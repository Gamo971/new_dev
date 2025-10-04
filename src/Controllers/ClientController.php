<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\ClientRepository;
use App\Models\Client;

class ClientController extends ApiController
{
    private ClientRepository $clientRepository;

    public function __construct(ClientRepository $clientRepository)
    {
        $this->clientRepository = $clientRepository;
    }

    public function index(): void
    {
        try {
            $clients = $this->clientRepository->findAll();
            $data = array_map(fn(Client $client) => $client->toArray(), $clients);
            
            $this->sendSuccess($data, 'Clients récupérés avec succès');
        } catch (\Exception $e) {
            $this->sendError('Erreur lors de la récupération des clients: ' . $e->getMessage(), 500);
        }
    }

    public function show(int $id): void
    {
        try {
            $client = $this->clientRepository->findById($id);
            
            if (!$client) {
                $this->sendError('Client non trouvé', 404);
                return;
            }
            
            $this->sendSuccess($client->toArray(), 'Client récupéré avec succès');
        } catch (\Exception $e) {
            $this->sendError('Erreur lors de la récupération du client: ' . $e->getMessage(), 500);
        }
    }

    public function store(): void
    {
        try {
            $data = $this->getRequestData();
            
            // Validation des champs requis
            $this->validateRequired($data, ['nom']);
            
            // Validation de l'email si fourni
            if (isset($data['email']) && !$this->validateEmail($data['email'])) {
                $this->sendError('Format d\'email invalide');
                return;
            }
            
            // Création du client
            $client = new Client(
                nom: $this->sanitizeString($data['nom']),
                email: $this->sanitizeString($data['email'] ?? null),
                telephone: $this->sanitizeString($data['telephone'] ?? null),
                adresse: $this->sanitizeString($data['adresse'] ?? null),
                ville: $this->sanitizeString($data['ville'] ?? null),
                codePostal: $this->sanitizeString($data['code_postal'] ?? null),
                pays: $this->sanitizeString($data['pays'] ?? 'France'),
                siret: $this->sanitizeString($data['siret'] ?? null),
                statut: $this->sanitizeString($data['statut'] ?? 'actif'),
                notes: $this->sanitizeString($data['notes'] ?? null)
            );
            
            $savedClient = $this->clientRepository->save($client);
            
            $this->sendSuccess($savedClient->toArray(), 'Client créé avec succès', 201);
        } catch (\Exception $e) {
            $this->sendError('Erreur lors de la création du client: ' . $e->getMessage(), 500);
        }
    }

    public function update(int $id): void
    {
        try {
            $client = $this->clientRepository->findById($id);
            
            if (!$client) {
                $this->sendError('Client non trouvé', 404);
                return;
            }
            
            $data = $this->getRequestData();
            
            // Validation de l'email si fourni
            if (isset($data['email']) && !$this->validateEmail($data['email'])) {
                $this->sendError('Format d\'email invalide');
                return;
            }
            
            // Mise à jour des champs
            if (isset($data['nom'])) {
                $client->setNom($this->sanitizeString($data['nom']));
            }
            if (isset($data['email'])) {
                $client->setEmail($this->sanitizeString($data['email']));
            }
            if (isset($data['telephone'])) {
                $client->setTelephone($this->sanitizeString($data['telephone']));
            }
            if (isset($data['adresse'])) {
                $client->setAdresse($this->sanitizeString($data['adresse']));
            }
            if (isset($data['ville'])) {
                $client->setVille($this->sanitizeString($data['ville']));
            }
            if (isset($data['code_postal'])) {
                $client->setCodePostal($this->sanitizeString($data['code_postal']));
            }
            if (isset($data['pays'])) {
                $client->setPays($this->sanitizeString($data['pays']));
            }
            if (isset($data['siret'])) {
                $client->setSiret($this->sanitizeString($data['siret']));
            }
            if (isset($data['statut'])) {
                $client->setStatut($this->sanitizeString($data['statut']));
            }
            if (isset($data['notes'])) {
                $client->setNotes($this->sanitizeString($data['notes']));
            }
            
            $updatedClient = $this->clientRepository->save($client);
            
            $this->sendSuccess($updatedClient->toArray(), 'Client mis à jour avec succès');
        } catch (\Exception $e) {
            $this->sendError('Erreur lors de la mise à jour du client: ' . $e->getMessage(), 500);
        }
    }

    public function delete(int $id): void
    {
        try {
            $client = $this->clientRepository->findById($id);
            
            if (!$client) {
                $this->sendError('Client non trouvé', 404);
                return;
            }
            
            $success = $this->clientRepository->delete($id);
            
            if ($success) {
                $this->sendSuccess([], 'Client supprimé avec succès');
            } else {
                $this->sendError('Erreur lors de la suppression du client');
            }
        } catch (\Exception $e) {
            $this->sendError('Erreur lors de la suppression du client: ' . $e->getMessage(), 500);
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
            
            $clients = $this->clientRepository->search($query);
            $data = array_map(fn(Client $client) => $client->toArray(), $clients);
            
            $this->sendSuccess($data, 'Recherche effectuée avec succès');
        } catch (\Exception $e) {
            $this->sendError('Erreur lors de la recherche: ' . $e->getMessage(), 500);
        }
    }

    public function statistiques(): void
    {
        try {
            $stats = $this->clientRepository->getStatistiques();
            $total = $this->clientRepository->count();
            
            $data = [
                'total' => $total,
                'par_statut' => $stats
            ];
            
            $this->sendSuccess($data, 'Statistiques récupérées avec succès');
        } catch (\Exception $e) {
            $this->sendError('Erreur lors de la récupération des statistiques: ' . $e->getMessage(), 500);
        }
    }
}
