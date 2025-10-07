/**
 * Modals - Gestion des fenêtres modales (création/édition)
 */

/**
 * Ouvre le modal de mission (création ou édition)
 */
async function openMissionModal(id = null) {
    const modal = document.getElementById('missionModal');
    const title = document.getElementById('missionModalTitle');
    const form = document.getElementById('missionForm');

    // Charger la liste des clients en premier
    await loadClientsForSelect('#missionClientId');

    if (id) {
        title.textContent = 'Modifier la Mission';
        await loadMissionData(id);
    } else {
        title.textContent = 'Nouvelle Mission';
        form.reset();
        document.getElementById('missionId').value = '';
    }
    
    modal.classList.remove('hidden');
}

/**
 * Ouvre le modal de tâche (création ou édition)
 */
async function openTacheModal(id = null) {
    const modal = document.getElementById('tacheModal');
    const title = document.getElementById('tacheModalTitle');
    const form = document.getElementById('tacheForm');

    // Charger la liste des missions en premier
    await loadMissionsForSelect('#tacheMissionId');

    if (id) {
        title.textContent = 'Modifier la Tâche';
        await loadTacheData(id);
    } else {
        title.textContent = 'Nouvelle Tâche';
        form.reset();
        document.getElementById('tacheId').value = '';
        // Masquer l'info de marge pour une nouvelle tâche
        document.getElementById('margeInfo').classList.add('hidden');
    }
    
    modal.classList.remove('hidden');
}

/**
 * Ouvre le modal de client (création ou édition)
 */
async function openClientModal(id = null) {
    const modal = document.getElementById('clientModal');
    const title = document.getElementById('clientModalTitle');
    const form = document.getElementById('clientForm');

    if (id) {
        title.textContent = 'Modifier le Client';
        await loadClientData(id);
    } else {
        title.textContent = 'Nouveau Client';
        form.reset();
        document.getElementById('clientId').value = '';
    }

    modal.classList.remove('hidden');
}

/**
 * Ouvre le modal de contact (création ou édition)
 */
async function openContactModal(id = null) {
    const modal = document.getElementById('contactModal');
    const title = document.getElementById('contactModalTitle');
    const form = document.getElementById('contactForm');

    // Charger la liste des clients en premier
    await loadClientsForSelect('#contactClientId');

    if (id) {
        title.textContent = 'Modifier le Contact';
        await loadContactData(id);
    } else {
        title.textContent = 'Nouveau Contact';
        form.reset();
        document.getElementById('contactId').value = '';
    }
    
    modal.classList.remove('hidden');
}

/**
 * Marque l'heure comme prioritaire (définie manuellement par l'utilisateur)
 */
function markHeurePrioritaire() {
    const heureInput = document.getElementById('tacheHeureDebutPlanifiee');
    const prioritaireInput = document.getElementById('tacheHeurePrioritaire');
    
    if (heureInput.value) {
        prioritaireInput.value = 'true';
        console.log('🕐 Heure marquée comme prioritaire (définie manuellement)');
    } else {
        prioritaireInput.value = 'false';
    }
}

/**
 * Ferme le modal de mission
 */
function closeMissionModal() {
    document.getElementById('missionModal').classList.add('hidden');
}

/**
 * Ferme le modal de tâche
 */
function closeTacheModal() {
    document.getElementById('tacheModal').classList.add('hidden');
}

/**
 * Ferme le modal de client
 */
function closeClientModal() {
    document.getElementById('clientModal').classList.add('hidden');
}

/**
 * Ferme le modal de contact
 */
function closeContactModal() {
    document.getElementById('contactModal').classList.add('hidden');
}

/**
 * Charge les données d'une mission pour édition
 */
async function loadMissionData(id) {
    try {
        const response = await fetch(`/api/missions/${id}`);
        const data = await response.json();
        
        if (data.success) {
            const mission = data.data;
            document.getElementById('missionId').value = mission.id;
            document.getElementById('missionClientId').value = mission.client_id;
            document.getElementById('missionNom').value = mission.nom;
            document.getElementById('missionDescription').value = mission.description || '';
            document.getElementById('missionStatut').value = mission.statut;
            document.getElementById('missionDateDebut').value = mission.date_debut || '';
            document.getElementById('missionDateFin').value = mission.date_fin_prevue || '';
            document.getElementById('missionBudget').value = mission.budget_prevu || '';
            // Afficher le temps estimé formaté (calculé automatiquement depuis les tâches)
            document.getElementById('missionTemps').value = mission.temps_estime_formate || '0min';
            document.getElementById('missionNotes').value = mission.notes || '';
        }
    } catch (error) {
        console.error('Erreur lors du chargement de la mission:', error);
    }
}

/**
 * Charge les données d'une tâche pour édition
 */
async function loadTacheData(id) {
    try {
        const response = await fetch(`/api/taches/${id}`);
        const data = await response.json();
        
        if (data.success) {
            const tache = data.data;
            document.getElementById('tacheId').value = tache.id;
            document.getElementById('tacheMissionId').value = tache.mission_id;
            document.getElementById('tacheNom').value = tache.nom;
            document.getElementById('tacheDescription').value = tache.description || '';
            document.getElementById('tacheStatut').value = tache.statut;
            document.getElementById('tacheDateEcheance').value = tache.date_echeance || '';
            document.getElementById('tacheDatePlanifiee').value = tache.date_planifiee || '';
            document.getElementById('tacheHeureDebutPlanifiee').value = tache.heure_debut_planifiee ? tache.heure_debut_planifiee.substring(0, 5) : '';
            document.getElementById('tacheAssigne').value = tache.assigne_a || '';
            document.getElementById('tacheTempsEstime').value = tache.temps_estime || '';
            document.getElementById('tacheOrdre').value = tache.ordre || '';
            document.getElementById('tacheNotes').value = tache.notes || '';
            
            // Mettre à jour l'affichage de la marge
            if (typeof updateMargeInfo === 'function') {
                updateMargeInfo();
            }
        }
    } catch (error) {
        console.error('Erreur lors du chargement de la tâche:', error);
    }
}

/**
 * Charge les données d'un client pour édition
 */
async function loadClientData(id) {
    try {
        const response = await fetch(`/api/clients/${id}`);
        const data = await response.json();
        
        if (data.success) {
            const client = data.data;
            document.getElementById('clientId').value = client.id;
            document.getElementById('clientNom').value = client.nom;
            document.getElementById('clientEmail').value = client.email || '';
            document.getElementById('clientTelephone').value = client.telephone || '';
            document.getElementById('clientAdresse').value = client.adresse || '';
            document.getElementById('clientVille').value = client.ville || '';
            document.getElementById('clientCodePostal').value = client.code_postal || '';
            document.getElementById('clientPays').value = client.pays || '';
            document.getElementById('clientSiret').value = client.siret || '';
            document.getElementById('clientStatut').value = client.statut;
            document.getElementById('clientNotes').value = client.notes || '';
        }
    } catch (error) {
        console.error('Erreur lors du chargement du client:', error);
    }
}

/**
 * Charge les données d'un contact pour édition
 */
async function loadContactData(id) {
    try {
        const response = await fetch(`/api/contacts/${id}`);
        const data = await response.json();
        
        if (data.success) {
            const contact = data.data;
            document.getElementById('contactId').value = contact.id;
            document.getElementById('contactClientId').value = contact.client_id;
            document.getElementById('contactPrenom').value = contact.prenom;
            document.getElementById('contactNom').value = contact.nom;
            document.getElementById('contactEmail').value = contact.email || '';
            document.getElementById('contactTelephone').value = contact.telephone || '';
            document.getElementById('contactPoste').value = contact.poste || '';
            document.getElementById('contactStatut').value = contact.statut;
            document.getElementById('contactNotes').value = contact.notes || '';
        }
    } catch (error) {
        console.error('Erreur lors du chargement du contact:', error);
    }
}

/**
 * Charge la liste des clients pour les select
 */
async function loadClientsForSelect(selectId) {
    try {
        const response = await fetch('/api/clients');
        const data = await response.json();
        
        if (data.success) {
            const select = document.querySelector(selectId);
            select.innerHTML = '<option value="">Sélectionner un client</option>';
            data.data.forEach(client => {
                const option = document.createElement('option');
                option.value = client.id;
                option.textContent = client.nom;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Erreur lors du chargement des clients:', error);
    }
}

/**
 * Charge la liste des missions pour les select
 */
async function loadMissionsForSelect(selectId) {
    try {
        const response = await fetch('/api/missions');
        const data = await response.json();
        
        if (data.success) {
            const select = document.querySelector(selectId);
            select.innerHTML = '<option value="">Sélectionner une mission</option>';
            data.data.forEach(mission => {
                const option = document.createElement('option');
                option.value = mission.id;
                option.textContent = mission.nom;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Erreur lors du chargement des missions:', error);
    }
}

/**
 * Sauvegarde une mission
 */
async function saveMission(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const data = Object.fromEntries(formData.entries());
    
    // Ne pas envoyer le temps estimé car il est calculé automatiquement depuis les tâches
    delete data.temps_estime;

    const missionId = document.getElementById('missionId').value;
    const url = missionId ? `/api/missions/${missionId}` : '/api/missions';
    const method = missionId ? 'PUT' : 'POST';

    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            closeMissionModal();
            loadMissions();
            loadStatistiques();
            showNotification('Mission sauvegardée avec succès', 'success');
        } else {
            showNotification('Erreur: ' + (result.error || 'Erreur inconnue'), 'error');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('Erreur lors de la sauvegarde', 'error');
    }
}

/**
 * Sauvegarde une tâche
 */
async function saveTache(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const data = Object.fromEntries(formData.entries());

    const tacheId = document.getElementById('tacheId').value;
    const url = tacheId ? `/api/taches/${tacheId}` : '/api/taches';
    const method = tacheId ? 'PUT' : 'POST';

    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            closeTacheModal();
            loadTaches();
            loadStatistiques();
            showNotification('Tâche sauvegardée avec succès', 'success');
        } else {
            showNotification('Erreur: ' + (result.error || 'Erreur inconnue'), 'error');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('Erreur lors de la sauvegarde', 'error');
    }
}

/**
 * Sauvegarde un client
 */
async function saveClient(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const data = Object.fromEntries(formData.entries());

    const clientId = document.getElementById('clientId').value;
    const url = clientId ? `/api/clients/${clientId}` : '/api/clients';
    const method = clientId ? 'PUT' : 'POST';

    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            closeClientModal();
            loadClients();
            loadStatistiques();
            showNotification('Client sauvegardé avec succès', 'success');
        } else {
            showNotification('Erreur: ' + (result.error || 'Erreur inconnue'), 'error');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('Erreur lors de la sauvegarde', 'error');
    }
}

/**
 * Sauvegarde un contact
 */
async function saveContact(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const data = Object.fromEntries(formData.entries());

    const contactId = document.getElementById('contactId').value;
    const url = contactId ? `/api/contacts/${contactId}` : '/api/contacts';
    const method = contactId ? 'PUT' : 'POST';

    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            closeContactModal();
            loadContacts();
            loadStatistiques();
            showNotification('Contact sauvegardé avec succès', 'success');
        } else {
            showNotification('Erreur: ' + (result.error || 'Erreur inconnue'), 'error');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('Erreur lors de la sauvegarde', 'error');
    }
}

