/**
 * Planning - Module principal de gestion du planning des tâches
 * Gère les vues multiples : Kanban, Agenda, Liste
 */

// Variables globales du planning
let currentPlanningView = 'kanban';
let calendarInstance = null;
let sortableInstances = [];

/**
 * Initialise le module de planning
 */
function initPlanning() {
    console.log('Initialisation du module Planning...');
    
    // Charger les tâches
    loadTaches();
    
    // Vue par défaut : Kanban
    showPlanningView('kanban');
}

/**
 * Change de vue de planning
 * @param {string} viewName - Nom de la vue (kanban, agenda, liste)
 */
function showPlanningView(viewName) {
    console.log(`Changement vers la vue : ${viewName}`);
    
    // Désactiver tous les boutons de vue
    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Activer le bouton de la vue courante
    const activeBtn = document.querySelector(`.view-btn[data-view="${viewName}"]`);
    if (activeBtn) {
        activeBtn.classList.add('active');
    }
    
    currentPlanningView = viewName;
    
    // Afficher la vue correspondante
    const container = document.getElementById('planning-container');
    
    switch(viewName) {
        case 'kanban':
            renderKanbanView(container);
            break;
        case 'agenda':
            renderAgendaView(container);
            break;
        case 'liste':
            renderListeView(container);
            break;
        default:
            console.error('Vue inconnue:', viewName);
    }
}

/**
 * Affiche la vue Liste (réutilise displayTaches)
 */
function renderListeView(container) {
    container.innerHTML = '<div id="tachesList" class="space-y-4"></div>';
    displayTaches(taches);
}

/**
 * Rafraîchit la vue courante
 */
function refreshPlanningView() {
    showPlanningView(currentPlanningView);
}

/**
 * Affiche l'onglet Planning
 */
function showPlanningTab() {
    // Charger les tâches si nécessaire
    if (taches.length === 0) {
        loadTaches().then(() => {
            showPlanningView(currentPlanningView);
        });
    } else {
        showPlanningView(currentPlanningView);
    }
}

