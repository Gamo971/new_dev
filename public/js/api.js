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

