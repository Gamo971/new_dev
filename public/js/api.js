/**
 * API - Gestion des appels vers l'API REST
 */

// Variables globales pour stocker les données
let missions = [];
let taches = [];
let clients = [];
let contacts = [];

/**
 * Charge toutes les données au démarrage
 */
async function loadAllData() {
    await Promise.all([
        loadMissions(),
        loadTaches(),
        loadClients(),
        loadContacts()
    ]);
}

/**
 * Charge les missions depuis l'API
 */
async function loadMissions() {
    try {
        const response = await fetch('/api/missions');
        const data = await response.json();
        
        if (data.success) {
            missions = data.data;
            displayMissions(missions);
        } else {
            console.error('Erreur lors du chargement des missions:', data.error);
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
}

/**
 * Charge les tâches depuis l'API
 */
async function loadTaches() {
    try {
        const response = await fetch('/api/taches');
        const data = await response.json();
        
        if (data.success) {
            taches = data.data;
            displayTaches(taches);
        } else {
            console.error('Erreur lors du chargement des tâches:', data.error);
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
}

/**
 * Charge les clients depuis l'API
 */
async function loadClients() {
    try {
        const response = await fetch('/api/clients');
        const data = await response.json();
        
        if (data.success) {
            clients = data.data;
            displayClients(clients);
        } else {
            console.error('Erreur lors du chargement des clients:', data.error);
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
}

/**
 * Charge les contacts depuis l'API
 */
async function loadContacts() {
    try {
        const response = await fetch('/api/contacts');
        const data = await response.json();
        
        if (data.success) {
            contacts = data.data;
            displayContacts(contacts);
        } else {
            console.error('Erreur lors du chargement des contacts:', data.error);
        }
    } catch (error) {
        console.error('Erreur:', error);
    }
}

/**
 * Charge les statistiques globales
 */
async function loadStatistiques() {
    try {
        const [missionsResponse, tachesResponse, clientsResponse, contactsResponse] = await Promise.all([
            fetch('/api/missions/statistiques'),
            fetch('/api/taches/statistiques'),
            fetch('/api/clients/statistiques'),
            fetch('/api/contacts/statistiques')
        ]);
        
        const [missionsData, tachesData, clientsData, contactsData] = await Promise.all([
            missionsResponse.json(),
            tachesResponse.json(),
            clientsResponse.json(),
            contactsResponse.json()
        ]);
        
        if (missionsData.success) {
            document.getElementById('totalMissions').textContent = missionsData.data.globales.total_missions || 0;
        }
        
        if (tachesData.success) {
            document.getElementById('totalTaches').textContent = tachesData.data.globales.total_taches || 0;
        }
        
        if (clientsData.success) {
            document.getElementById('totalClients').textContent = clientsData.data.total || 0;
        }
        
        if (contactsData.success) {
            document.getElementById('totalContacts').textContent = contactsData.data.total || 0;
        }
    } catch (error) {
        console.error('Erreur lors du chargement des statistiques:', error);
    }
}

/**
 * Supprime une mission
 */
async function deleteMission(id) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cette mission ?')) {
        return;
    }

    try {
        const response = await fetch(`/api/missions/${id}`, {
            method: 'DELETE'
        });

        const data = await response.json();

        if (data.success) {
            loadMissions();
            loadStatistiques();
            showNotification('Mission supprimée avec succès', 'success');
        } else {
            showNotification('Erreur: ' + (data.error || 'Erreur lors de la suppression'), 'error');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('Erreur lors de la suppression', 'error');
    }
}

/**
 * Supprime une tâche
 */
async function deleteTache(id) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cette tâche ?')) {
        return;
    }

    try {
        const response = await fetch(`/api/taches/${id}`, {
            method: 'DELETE'
        });

        const data = await response.json();

        if (data.success) {
            loadTaches();
            loadStatistiques();
            showNotification('Tâche supprimée avec succès', 'success');
        } else {
            showNotification('Erreur: ' + (data.error || 'Erreur lors de la suppression'), 'error');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('Erreur lors de la suppression', 'error');
    }
}

/**
 * Supprime un client
 */
async function deleteClient(id) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce client ?')) {
        return;
    }

    try {
        const response = await fetch(`/api/clients/${id}`, {
            method: 'DELETE'
        });

        const data = await response.json();

        if (data.success) {
            loadClients();
            loadStatistiques();
            showNotification('Client supprimé avec succès', 'success');
        } else {
            showNotification('Erreur: ' + (data.error || 'Erreur lors de la suppression'), 'error');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('Erreur lors de la suppression', 'error');
    }
}

/**
 * Supprime un contact
 */
async function deleteContact(id) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce contact ?')) {
        return;
    }

    try {
        const response = await fetch(`/api/contacts/${id}`, {
            method: 'DELETE'
        });

        const data = await response.json();

        if (data.success) {
            loadContacts();
            loadStatistiques();
            showNotification('Contact supprimé avec succès', 'success');
        } else {
            showNotification('Erreur: ' + (data.error || 'Erreur lors de la suppression'), 'error');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('Erreur lors de la suppression', 'error');
    }
}

/**
 * Charge les tâches d'une mission spécifique
 */
async function loadMissionTaches(missionId) {
    try {
        const response = await fetch(`/api/taches/mission/${missionId}`);
        const data = await response.json();
        
        if (data.success) {
            return data.data;
        } else {
            console.error('Erreur lors du chargement des tâches:', data.error);
            return [];
        }
    } catch (error) {
        console.error('Erreur:', error);
        return [];
    }
}

/**
 * Affiche/masque les tâches d'une mission (accordéon)
 */
async function toggleMissionTaches(missionId, event) {
    event.stopPropagation();
    
    const container = document.getElementById(`mission-taches-${missionId}`);
    const icon = document.getElementById(`mission-taches-icon-${missionId}`);
    
    if (!container) return;
    
    // Si le conteneur est visible, on le masque
    if (!container.classList.contains('hidden')) {
        container.classList.add('hidden');
        icon.classList.remove('rotate-180');
        return;
    }
    
    // Afficher le conteneur et charger les tâches
    container.classList.remove('hidden');
    icon.classList.add('rotate-180');
    
    // Charger les tâches de la mission
    const taches = await loadMissionTaches(missionId);
    
    // Afficher les tâches
    if (taches.length === 0) {
        container.innerHTML = `
            <div class="text-center text-gray-500 py-8">
                <i class="fas fa-inbox text-4xl mb-3 text-gray-400"></i>
                <p>Aucune tâche pour cette mission</p>
                <button onclick="openTacheModalForMission(${missionId})" 
                        class="mt-4 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>Ajouter une tâche
                </button>
            </div>
        `;
    } else {
        // Construire le HTML des tâches
        const tachesHTML = taches.map(tache => {
            // Construire les badges
            const badgePriorite = Badge(tache.priorite_libelle || 'Inconnue', tache.priorite_couleur || 'bg-gray-100 text-gray-800');
            const badgeStatut = Badge(tache.statut_libelle || 'Inconnu', 'bg-gray-100 text-gray-800');
            
            // Construire les boutons d'action
            const btnEdit = ActionButton('fa-edit', `openTacheModal(${tache.id})`, 'Modifier', 'text-blue-600 hover:text-blue-800 text-xs');
            const btnDelete = ActionButton('fa-trash', `deleteTache(${tache.id})`, 'Supprimer', 'text-red-600 hover:text-red-800 text-xs');
            
            // Construire les informations
            const infoEcheance = tache.date_echeance ? 
                `<div><i class="fas fa-flag-checkered mr-1 text-red-500"></i>Échéance: ${formatDate(tache.date_echeance)}</div>` : '';
            const infoPlanifiee = tache.date_planifiee ? 
                `<div><i class="fas fa-calendar-check mr-1 text-blue-500"></i>Planifiée: ${formatDate(tache.date_planifiee)}</div>` : '';
            const infoTemps = tache.temps_estime ? 
                `<div><i class="fas fa-clock mr-1"></i>Temps: ${tache.temps_estime_formate || tache.temps_estime + 'min'}</div>` : '';
            const infoAssigne = tache.assigne_a ? 
                `<div><i class="fas fa-user mr-1"></i>${tache.assigne_a}</div>` : '';
            
            return `
                <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start mb-2">
                        <h5 class="font-semibold text-gray-800 text-sm">${tache.nom || 'Sans nom'}</h5>
                        <div class="flex gap-1">
                            ${btnEdit}
                            ${btnDelete}
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 mb-3">
                        ${badgePriorite}
                        ${badgeStatut}
                    </div>
                    ${tache.description ? `<p class="text-xs text-gray-600 mb-2">${tache.description}</p>` : ''}
                    <div class="text-xs text-gray-500 space-y-1">
                        ${infoEcheance}
                        ${infoPlanifiee}
                        ${infoTemps}
                        ${infoAssigne}
                    </div>
                </div>
            `;
        }).join('');
        
        container.innerHTML = `
            <div class="mb-4 flex justify-between items-center">
                <h4 class="font-semibold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-list-check text-green-600"></i>
                    Tâches de la mission (${taches.length})
                </h4>
                <button onclick="openTacheModalForMission(${missionId})" 
                        class="px-3 py-1 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>Ajouter une tâche
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                ${tachesHTML}
            </div>
        `;
    }
}

/**
 * Ouvre le modal de tâche pré-rempli avec une mission
 */
async function openTacheModalForMission(missionId) {
    await openTacheModal();
    document.getElementById('tacheMissionId').value = missionId;
}

