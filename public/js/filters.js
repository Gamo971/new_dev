/**
 * Filtres - Gestion du filtrage et du tri
 */

/**
 * Configure les écouteurs d'événements pour les filtres
 */
function setupEventListeners() {
    // Recherche des missions
    document.getElementById('missionSearch').addEventListener('input', filterMissions);
    document.getElementById('missionStatutFilter').addEventListener('change', filterMissions);
    document.getElementById('missionPrioriteFilter').addEventListener('change', filterMissions);
    
    // Recherche des tâches
    document.getElementById('tacheSearch').addEventListener('input', filterTaches);
    document.getElementById('tacheSortBy').addEventListener('change', filterTaches);
    
    // Filtres de statut des tâches (checkboxes)
    document.querySelectorAll('.tache-statut-filter').forEach(checkbox => {
        checkbox.addEventListener('change', filterTaches);
    });
    
    // Filtres de priorité des tâches (checkboxes)
    document.querySelectorAll('.tache-priorite-filter').forEach(checkbox => {
        checkbox.addEventListener('change', filterTaches);
    });
    
    // Recherche des clients
    document.getElementById('clientSearch').addEventListener('input', filterClients);
    document.getElementById('clientStatutFilter').addEventListener('change', filterClients);
    
    // Recherche des contacts
    document.getElementById('contactSearch').addEventListener('input', filterContacts);
    document.getElementById('contactStatutFilter').addEventListener('change', filterContacts);
}

/**
 * Filtre les missions selon les critères de recherche
 */
function filterMissions() {
    const search = document.getElementById('missionSearch').value.toLowerCase();
    const statutFilter = document.getElementById('missionStatutFilter').value;
    const prioriteFilter = document.getElementById('missionPrioriteFilter').value;
    
    let filtered = missions.filter(mission => {
        const matchesSearch = !search || 
            mission.nom.toLowerCase().includes(search) ||
            (mission.description && mission.description.toLowerCase().includes(search)) ||
            (mission.client_nom && mission.client_nom.toLowerCase().includes(search));
        
        const matchesStatut = !statutFilter || mission.statut === statutFilter;
        const matchesPriorite = !prioriteFilter || mission.priorite === prioriteFilter;
        
        return matchesSearch && matchesStatut && matchesPriorite;
    });
    
    displayMissions(filtered);
}

/**
 * Filtre les tâches selon les critères de recherche
 */
function filterTaches() {
    const search = document.getElementById('tacheSearch').value.toLowerCase();
    
    // Récupérer les statuts cochés
    const selectedStatuts = Array.from(document.querySelectorAll('.tache-statut-filter:checked'))
        .map(cb => cb.value);
    
    // Récupérer les priorités cochées
    const selectedPriorites = Array.from(document.querySelectorAll('.tache-priorite-filter:checked'))
        .map(cb => cb.value);
    
    let filtered = taches.filter(tache => {
        const matchesSearch = !search || 
            tache.nom.toLowerCase().includes(search) ||
            (tache.description && tache.description.toLowerCase().includes(search)) ||
            (tache.mission_nom && tache.mission_nom.toLowerCase().includes(search));
        
        // Si aucun statut n'est coché, on affiche tout
        const matchesStatut = selectedStatuts.length === 0 || selectedStatuts.includes(tache.statut);
        
        // Si aucune priorité n'est cochée, on affiche tout
        const matchesPriorite = selectedPriorites.length === 0 || selectedPriorites.includes(tache.priorite);
        
        return matchesSearch && matchesStatut && matchesPriorite;
    });
    
    displayTaches(filtered);
}

/**
 * Filtre les clients selon les critères de recherche
 */
function filterClients() {
    const search = document.getElementById('clientSearch').value.toLowerCase();
    const statutFilter = document.getElementById('clientStatutFilter').value;
    
    let filtered = clients.filter(client => {
        const matchesSearch = !search || 
            client.nom.toLowerCase().includes(search) ||
            (client.email && client.email.toLowerCase().includes(search)) ||
            (client.ville && client.ville.toLowerCase().includes(search));
        
        const matchesStatut = !statutFilter || client.statut === statutFilter;
        
        return matchesSearch && matchesStatut;
    });
    
    displayClients(filtered);
}

/**
 * Filtre les contacts selon les critères de recherche
 */
function filterContacts() {
    const search = document.getElementById('contactSearch').value.toLowerCase();
    const statutFilter = document.getElementById('contactStatutFilter').value;
    
    let filtered = contacts.filter(contact => {
        const matchesSearch = !search || 
            contact.nom_complet.toLowerCase().includes(search) ||
            (contact.email && contact.email.toLowerCase().includes(search)) ||
            (contact.poste && contact.poste.toLowerCase().includes(search));
        
        const matchesStatut = !statutFilter || contact.statut === statutFilter;
        
        return matchesSearch && matchesStatut;
    });
    
    displayContacts(filtered);
}

/**
 * Affiche ou masque les filtres des tâches
 */
function toggleTacheFilters() {
    const content = document.getElementById('tacheFiltersContent');
    const icon = document.getElementById('tacheFiltersIcon');
    
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        icon.classList.add('rotate-180');
    } else {
        content.classList.add('hidden');
        icon.classList.remove('rotate-180');
    }
}

/**
 * Trie les tâches selon le critère sélectionné
 */
function sortTaches(tachesToSort) {
    const sortBy = document.getElementById('tacheSortBy').value;
    const sorted = [...tachesToSort];
    
    const prioriteOrder = { 'urgente': 4, 'haute': 3, 'normale': 2, 'basse': 1 };
    const statutOrder = { 'a_faire': 1, 'en_cours': 2, 'terminee': 3, 'annulee': 4 };
    
    switch(sortBy) {
        case 'date_creation_desc':
            sorted.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
            break;
        case 'date_creation_asc':
            sorted.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
            break;
        case 'priorite_desc':
            sorted.sort((a, b) => (prioriteOrder[b.priorite] || 0) - (prioriteOrder[a.priorite] || 0));
            break;
        case 'priorite_asc':
            sorted.sort((a, b) => (prioriteOrder[a.priorite] || 0) - (prioriteOrder[b.priorite] || 0));
            break;
        case 'echeance_asc':
            sorted.sort((a, b) => {
                if (!a.date_echeance && !b.date_echeance) return 0;
                if (!a.date_echeance) return 1;
                if (!b.date_echeance) return -1;
                return new Date(a.date_echeance) - new Date(b.date_echeance);
            });
            break;
        case 'echeance_desc':
            sorted.sort((a, b) => {
                if (!a.date_echeance && !b.date_echeance) return 0;
                if (!a.date_echeance) return 1;
                if (!b.date_echeance) return -1;
                return new Date(b.date_echeance) - new Date(a.date_echeance);
            });
            break;
        case 'statut':
            sorted.sort((a, b) => (statutOrder[a.statut] || 0) - (statutOrder[b.statut] || 0));
            break;
        case 'nom_asc':
            sorted.sort((a, b) => a.nom.localeCompare(b.nom));
            break;
        case 'nom_desc':
            sorted.sort((a, b) => b.nom.localeCompare(a.nom));
            break;
        case 'mission':
            sorted.sort((a, b) => (a.mission_nom || '').localeCompare(b.mission_nom || ''));
            break;
        case 'temps_estime_desc':
            sorted.sort((a, b) => (b.temps_estime || 0) - (a.temps_estime || 0));
            break;
        case 'temps_estime_asc':
            sorted.sort((a, b) => (a.temps_estime || 0) - (b.temps_estime || 0));
            break;
        default:
            // Par défaut: date de création descendante
            sorted.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
    }
    
    return sorted;
}

