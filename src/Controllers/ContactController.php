<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\ContactRepository;
use App\Models\Contact;

class ContactController extends ApiController
{
    private ContactRepository $contactRepository;

    public function __construct(ContactRepository $contactRepository)
    {
        $this->contactRepository = $contactRepository;
    }

    public function index(): void
    {
        try {
            $data = $this->contactRepository->findAll();
            
            $this->sendSuccess($data, 'Contacts récupérés avec succès');
        } catch (\Exception $e) {
            $this->sendError('Erreur lors de la récupération des contacts: ' . $e->getMessage(), 500);
        }
    }

    public function show(int $id): void
    {
        try {
            $contact = $this->contactRepository->findById($id);
            
            if (!$contact) {
                $this->sendError('Contact non trouvé', 404);
                return;
            }
            
            $this->sendSuccess($contact->toArray(), 'Contact récupéré avec succès');
        } catch (\Exception $e) {
            $this->sendError('Erreur lors de la récupération du contact: ' . $e->getMessage(), 500);
        }
    }

    public function store(): void
    {
        try {
            $data = $this->getRequestData();
            
            // Validation des champs requis
            $this->validateRequired($data, ['client_id', 'prenom', 'nom']);
            
            // Validation de l'email si fourni
            if (isset($data['email']) && !$this->validateEmail($data['email'])) {
                $this->sendError('Format d\'email invalide');
                return;
            }
            
            // Création du contact
            $contact = new Contact(
                clientId: (int) $data['client_id'],
                prenom: $this->sanitizeString($data['prenom']),
                nom: $this->sanitizeString($data['nom']),
                email: $this->sanitizeString($data['email'] ?? null),
                telephone: $this->sanitizeString($data['telephone'] ?? null),
                poste: $this->sanitizeString($data['poste'] ?? null),
                statut: $this->sanitizeString($data['statut'] ?? 'actif'),
                notes: $this->sanitizeString($data['notes'] ?? null)
            );
            
            $savedContact = $this->contactRepository->save($contact);
            
            $this->sendSuccess($savedContact->toArray(), 'Contact créé avec succès', 201);
        } catch (\Exception $e) {
            $this->sendError('Erreur lors de la création du contact: ' . $e->getMessage(), 500);
        }
    }

    public function update(int $id): void
    {
        try {
            $contact = $this->contactRepository->findById($id);
            
            if (!$contact) {
                $this->sendError('Contact non trouvé', 404);
                return;
            }
            
            $data = $this->getRequestData();
            
            // Validation de l'email si fourni
            if (isset($data['email']) && !$this->validateEmail($data['email'])) {
                $this->sendError('Format d\'email invalide');
                return;
            }
            
            // Mise à jour des champs
            if (isset($data['client_id'])) {
                $contact->setClientId((int) $data['client_id']);
            }
            if (isset($data['prenom'])) {
                $contact->setPrenom($this->sanitizeString($data['prenom']));
            }
            if (isset($data['nom'])) {
                $contact->setNom($this->sanitizeString($data['nom']));
            }
            if (isset($data['email'])) {
                $contact->setEmail($this->sanitizeString($data['email']));
            }
            if (isset($data['telephone'])) {
                $contact->setTelephone($this->sanitizeString($data['telephone']));
            }
            if (isset($data['poste'])) {
                $contact->setPoste($this->sanitizeString($data['poste']));
            }
            if (isset($data['statut'])) {
                $contact->setStatut($this->sanitizeString($data['statut']));
            }
            if (isset($data['notes'])) {
                $contact->setNotes($this->sanitizeString($data['notes']));
            }
            
            $updatedContact = $this->contactRepository->save($contact);
            
            $this->sendSuccess($updatedContact->toArray(), 'Contact mis à jour avec succès');
        } catch (\Exception $e) {
            $this->sendError('Erreur lors de la mise à jour du contact: ' . $e->getMessage(), 500);
        }
    }

    public function delete(int $id): void
    {
        try {
            $contact = $this->contactRepository->findById($id);
            
            if (!$contact) {
                $this->sendError('Contact non trouvé', 404);
                return;
            }
            
            $success = $this->contactRepository->delete($id);
            
            if ($success) {
                $this->sendSuccess([], 'Contact supprimé avec succès');
            } else {
                $this->sendError('Erreur lors de la suppression du contact');
            }
        } catch (\Exception $e) {
            $this->sendError('Erreur lors de la suppression du contact: ' . $e->getMessage(), 500);
        }
    }

    public function findByClient(int $clientId): void
    {
        try {
            $contacts = $this->contactRepository->findByClientId($clientId);
            $data = array_map(fn(Contact $contact) => $contact->toArray(), $contacts);
            
            $this->sendSuccess($data, 'Contacts du client récupérés avec succès');
        } catch (\Exception $e) {
            $this->sendError('Erreur lors de la récupération des contacts: ' . $e->getMessage(), 500);
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
            
            $contacts = $this->contactRepository->search($query);
            $data = array_map(fn(Contact $contact) => $contact->toArray(), $contacts);
            
            $this->sendSuccess($data, 'Recherche effectuée avec succès');
        } catch (\Exception $e) {
            $this->sendError('Erreur lors de la recherche: ' . $e->getMessage(), 500);
        }
    }

    public function statistiques(): void
    {
        try {
            $stats = $this->contactRepository->getStatistiques();
            $total = $this->contactRepository->count();
            
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
