/**
 * Vue Kanban - Gestion du tableau Kanban avec drag & drop
 */

/**
 * Rend la vue Kanban
 * @param {HTMLElement} container - Conteneur pour la vue
 */
function renderKanbanView(container) {
    // Structure des colonnes
    const colonnes = [
        { id: 'a_faire', label: '📝 À faire', color: 'blue' },
        { id: 'en_cours', label: '⚙️ En cours', color: 'yellow' },
        { id: 'terminee', label: '✅ Terminée', color: 'green' },
        { id: 'annulee', label: '❌ Annulée', color: 'red' }
    ];
    
    // Grouper les tâches par statut
    const tachesParStatut = {};
    colonnes.forEach(col => {
        tachesParStatut[col.id] = taches.filter(t => t.statut === col.id);
    });
    
    // Générer le HTML du board
    container.innerHTML = `
        <div class="kanban-board grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            ${colonnes.map(col => renderKanbanColumn(col, tachesParStatut[col.id])).join('')}
        </div>
    `;
    
    // Initialiser Sortable.js pour chaque colonne
    initKanbanDragDrop();
}

/**
 * Rend une colonne Kanban
 * @param {Object} colonne - Configuration de la colonne
 * @param {Array} taches - Tâches de cette colonne
 * @returns {string} HTML de la colonne
 */
function renderKanbanColumn(colonne, taches) {
    const count = taches.length;
    
    return `
        <div class="kanban-column bg-gray-50 rounded-lg p-4">
            <div class="kanban-header mb-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">
                    ${colonne.label}
                </h3>
                <span class="px-2 py-1 bg-${colonne.color}-100 text-${colonne.color}-800 rounded-full text-sm font-medium">
                    ${count}
                </span>
            </div>
            <div class="kanban-tasks space-y-3 min-h-[200px]" 
                 data-status="${colonne.id}"
                 id="kanban-${colonne.id}">
                ${taches.map(t => renderKanbanCard(t)).join('')}
            </div>
        </div>
    `;
}

/**
 * Rend une carte Kanban
 * @param {Object} tache - Tâche à afficher
 * @returns {string} HTML de la carte
 */
function renderKanbanCard(tache) {
    return `
        <div class="kanban-card bg-white rounded-lg p-4 shadow-sm border border-gray-200 cursor-move hover:shadow-md transition-shadow"
             data-id="${tache.id}">
            <div class="flex items-start justify-between mb-2">
                <div class="flex-1">
                    <h4 class="font-semibold text-gray-800 text-sm mb-1">${tache.nom}</h4>
                    ${tache.mission_nom ? `<p class="text-xs text-gray-500">📁 ${tache.mission_nom}</p>` : ''}
                </div>
            </div>
            
            ${tache.description ? `<p class="text-xs text-gray-600 mb-2 line-clamp-2">${tache.description}</p>` : ''}
            
            <div class="flex items-center justify-between text-xs text-gray-500">
                <div class="flex items-center gap-2">
                    ${tache.date_echeance ? `
                        <span class="flex items-center gap-1 ${isOverdue(tache.date_echeance) ? 'text-red-600 font-semibold' : ''}">
                            <i class="fas fa-clock"></i>
                            ${formatDate(tache.date_echeance)}
                        </span>
                    ` : ''}
                    ${tache.temps_estime ? `
                        <span class="flex items-center gap-1">
                            <i class="fas fa-hourglass-half"></i>
                            ${tache.temps_estime_formate}
                        </span>
                    ` : ''}
                </div>
                <button onclick="openTacheModal(${tache.id})" 
                        class="text-blue-600 hover:text-blue-800" 
                        title="Modifier">
                    <i class="fas fa-edit"></i>
                </button>
            </div>
        </div>
    `;
}

/**
 * Initialise le drag & drop avec Sortable.js
 */
function initKanbanDragDrop() {
    // Détruire les instances précédentes
    sortableInstances.forEach(instance => instance.destroy());
    sortableInstances = [];
    
    // Initialiser Sortable pour chaque colonne
    const colonnes = document.querySelectorAll('.kanban-tasks');
    
    colonnes.forEach(colonne => {
        const sortable = Sortable.create(colonne, {
            group: 'kanban',
            animation: 150,
            ghostClass: 'kanban-ghost',
            dragClass: 'kanban-drag',
            
            onEnd: function(evt) {
                const tacheId = parseInt(evt.item.dataset.id);
                const newStatus = evt.to.dataset.status;
                const oldStatus = evt.from.dataset.status;
                
                // Si le statut a changé
                if (newStatus !== oldStatus) {
                    updateTacheStatus(tacheId, newStatus);
                }
            }
        });
        
        sortableInstances.push(sortable);
    });
}

/**
 * Met à jour le statut d'une tâche
 * @param {number} tacheId - ID de la tâche
 * @param {string} newStatus - Nouveau statut
 */
async function updateTacheStatus(tacheId, newStatus) {
    try {
        const response = await fetch(`/api/taches/${tacheId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ statut: newStatus })
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Mettre à jour localement
            const tache = taches.find(t => t.id === tacheId);
            if (tache) {
                tache.statut = newStatus;
            }
            
            showNotification('Tâche déplacée avec succès', 'success');
            
            // Rafraîchir les compteurs
            refreshPlanningView();
        } else {
            showNotification('Erreur: ' + (result.error || 'Impossible de déplacer la tâche'), 'error');
            // Recharger pour annuler le déplacement visuel
            loadTaches().then(() => refreshPlanningView());
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('Erreur lors du déplacement', 'error');
        loadTaches().then(() => refreshPlanningView());
    }
}

/**
 * Vérifie si une date est dépassée
 * @param {string} dateString - Date au format ISO
 * @returns {boolean} True si la date est dépassée
 */
function isOverdue(dateString) {
    if (!dateString) return false;
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const echeance = new Date(dateString);
    echeance.setHours(0, 0, 0, 0);
    return echeance < today;
}

