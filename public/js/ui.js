/**
 * UI - Gestion de l'affichage et du rendu
 * Utilise les composants réutilisables de components.js
 */

// Variable pour suivre l'onglet actuel
let currentTab = 'missions';

/**
 * Affiche un onglet spécifique
 */
function showTab(tabName) {
    // Masquer tous les onglets
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Désactiver tous les boutons d'onglets
    document.querySelectorAll('.tab').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Afficher l'onglet sélectionné
    document.getElementById(tabName).classList.add('active');
    
    // Activer le bouton d'onglet sélectionné
    event.target.classList.add('active');
    
    currentTab = tabName;
    
    // Charger les données spécifiques à l'onglet
    switch(tabName) {
        case 'missions':
            loadMissions();
            break;
        case 'taches':
            loadTaches();
            break;
        case 'clients':
            loadClients();
            break;
        case 'contacts':
            loadContacts();
            break;
        case 'statistiques':
            loadStatistiques();
            break;
    }
}

/**
 * Affiche la liste des missions en utilisant le composant Card
 */
function displayMissions(missionsToShow) {
    const container = document.getElementById('missionsList');
    
    if (missionsToShow.length === 0) {
        container.innerHTML = EmptyState('Aucune mission trouvée', 'fa-tasks');
        return;
    }
    
    // Appliquer le tri
    const sortedMissions = sortMissions(missionsToShow);
    
    container.innerHTML = sortedMissions.map(mission => {
        // Construction des badges
        const badges = Badge(mission.priorite_libelle, mission.priorite_couleur) + 
                      Badge(mission.statut_libelle, 'bg-gray-100 text-gray-800');
        
        // Construction des champs d'information
        const fields = [
            InfoField('Début', mission.date_debut ? formatDate(mission.date_debut) : ''),
            InfoField('Fin prévue', mission.date_fin_prevue ? formatDate(mission.date_fin_prevue) : ''),
            InfoField('Budget', mission.budget_prevu ? formatCurrency(mission.budget_prevu) : ''),
            InfoField('Temps', mission.temps_estime_formate)
        ].join('');
        
        // Construction du footer avec bouton pour afficher les tâches
        const footer = `
            <div class="flex justify-between items-center w-full">
                <div class="text-sm text-gray-500">
                    Créée le ${formatDateTime(mission.created_at)}
                </div>
                <div class="flex gap-2">
                    <button onclick="toggleMissionTaches(${mission.id}, event)" 
                            class="px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors flex items-center gap-2"
                            title="Voir les tâches">
                        <i class="fas fa-tasks"></i>
                        <span>Tâches</span>
                        <i id="mission-taches-icon-${mission.id}" class="fas fa-chevron-down transition-transform"></i>
                    </button>
                    ${ActionButtons('mission', mission.id)}
                </div>
            </div>
        `;
        
        // Créer la carte avec un conteneur pour les tâches
        return `
            <div class="mission-card-wrapper">
                ${Card({
                    title: mission.nom,
                    subtitle: `Client: ${mission.client_nom || 'Non défini'}`,
                    badges: badges,
                    description: mission.description || '',
                    fields: fields,
                    footer: footer
                })}
                <div id="mission-taches-${mission.id}" class="mission-taches-container hidden bg-gray-50 border border-t-0 border-gray-200 rounded-b-lg p-4 -mt-2">
                    <div class="flex items-center justify-center py-4">
                        <i class="fas fa-spinner fa-spin text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

/**
 * Affiche la liste des tâches en utilisant le composant Card
 */
function displayTaches(tachesToShow) {
    const container = document.getElementById('tachesList');
    
    if (tachesToShow.length === 0) {
        container.innerHTML = EmptyState('Aucune tâche trouvée', 'fa-list-check');
        return;
    }
    
    // Appliquer le tri
    const sortedTaches = sortTaches(tachesToShow);
    
    container.innerHTML = sortedTaches.map(tache => {
        // Construction des badges
        const badges = Badge(tache.priorite_libelle, tache.priorite_couleur) + 
                      Badge(tache.statut_libelle, 'bg-gray-100 text-gray-800');
        
        // Construction des champs d'information
        const fields = [
            InfoField('Échéance', tache.date_echeance ? formatDate(tache.date_echeance) : ''),
            InfoField('Temps estimé', tache.temps_estime_formate),
            InfoField('Temps réel', tache.temps_reel_formate),
            InfoField('Assigné à', tache.assigne_a || '')
        ].join('');
        
        // Construction du footer
        const footer = `
            <div class="text-sm text-gray-500">
                Créée le ${formatDateTime(tache.created_at)}
            </div>
            ${ActionButtons('tache', tache.id)}
        `;
        
        return Card({
            title: tache.nom,
            subtitle: `Mission: ${tache.mission_nom || 'Non définie'}`,
            badges: badges,
            description: tache.description || '',
            fields: fields,
            footer: footer
        });
    }).join('');
}

/**
 * Affiche la liste des clients en utilisant le composant Card
 */
function displayClients(clientsToShow) {
    const container = document.getElementById('clientsList');
    
    if (clientsToShow.length === 0) {
        container.innerHTML = EmptyState('Aucun client trouvé', 'fa-building');
        return;
    }
    
    container.innerHTML = clientsToShow.map(client => {
        // Construction des badges
        const statusColor = client.statut === 'actif' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
        const badges = Badge(client.statut_libelle, statusColor);
        
        // Construction des champs d'information
        const fields = [
            InfoField('Email', client.email || ''),
            InfoField('Téléphone', client.telephone || ''),
            InfoField('SIRET', client.siret || '')
        ].filter(f => f).join('');
        
        // Construction du footer
        const footer = `
            <div class="text-sm text-gray-500">
                Créé le ${formatDateTime(client.created_at)}
            </div>
            ${ActionButtons('client', client.id)}
        `;
        
        return Card({
            title: client.nom,
            subtitle: client.adresse_complete || 'Adresse non définie',
            badges: badges,
            fields: fields,
            footer: footer
        });
    }).join('');
}

/**
 * Affiche la liste des contacts en utilisant le composant Card
 */
function displayContacts(contactsToShow) {
    const container = document.getElementById('contactsList');
    
    if (contactsToShow.length === 0) {
        container.innerHTML = EmptyState('Aucun contact trouvé', 'fa-address-book');
        return;
    }
    
    container.innerHTML = contactsToShow.map(contact => {
        // Construction des badges
        const statusColor = contact.statut === 'actif' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
        const badges = Badge(contact.statut_libelle, statusColor);
        
        // Construction des champs d'information
        const fields = [
            InfoField('Email', contact.email || ''),
            InfoField('Téléphone', contact.telephone || ''),
            InfoField('Client', contact.client_id)
        ].filter(f => f).join('');
        
        // Construction du footer
        const footer = `
            <div class="text-sm text-gray-500">
                Créé le ${formatDateTime(contact.created_at)}
            </div>
            ${ActionButtons('contact', contact.id)}
        `;
        
        return Card({
            title: contact.nom_complet,
            subtitle: contact.poste || 'Poste non défini',
            badges: badges,
            fields: fields,
            footer: footer
        });
    }).join('');
}
