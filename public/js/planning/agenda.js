/**
 * Vue Agenda - Calendrier avec FullCalendar
 */

/**
 * Rend la vue Agenda (calendrier)
 * @param {HTMLElement} container - Conteneur pour la vue
 */
function renderAgendaView(container) {
    container.innerHTML = `
        <div class="agenda-view">
            <div id="calendar"></div>
        </div>
    `;
    
    // Attendre que le DOM soit mis à jour
    setTimeout(() => {
        initFullCalendar();
    }, 100);
}

/**
 * Initialise FullCalendar
 */
function initFullCalendar() {
    const calendarEl = document.getElementById('calendar');
    
    if (!calendarEl) {
        console.error('Élément calendar non trouvé');
        return;
    }
    
    // Détruire l'instance précédente si elle existe
    if (calendarInstance) {
        calendarInstance.destroy();
    }
    
    // Mapper les tâches vers les événements
    const events = [];
    
    taches.forEach(t => {
        // Afficher la tâche sur sa date de planification si elle existe
        if (t.date_planifiee && t.statut !== 'terminee') {
            events.push({
                id: t.id,
                title: `📅 ${t.nom}`,
                start: t.date_planifiee,
                allDay: true,
                backgroundColor: getPlanificationColor(t),
                borderColor: getPriorityColor(t.priorite),
                borderWidth: 2,
                extendedProps: {
                    tache: t,
                    type: 'planifiee'
                }
            });
        }
        
        // Afficher aussi l'échéance si elle existe (en grisé si déjà planifiée)
        if (t.date_echeance && t.statut !== 'terminee') {
            events.push({
                id: `echeance-${t.id}`,
                title: `🏁 ${t.nom} (échéance)`,
                start: t.date_echeance,
                allDay: true,
                backgroundColor: t.date_planifiee ? '#cbd5e1' : getPriorityColor(t.priorite),
                borderColor: getPriorityColor(t.priorite),
                display: t.date_planifiee ? 'background' : 'auto',
                extendedProps: {
                    tache: t,
                    type: 'echeance'
                }
            });
        }
    });
    
    // Créer le calendrier
    calendarInstance = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'fr',
        firstDay: 1, // Lundi
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
        },
        buttonText: {
            today: 'Aujourd\'hui',
            month: 'Mois',
            week: 'Semaine',
            list: 'Liste'
        },
        events: events,
        eventClick: function(info) {
            info.jsEvent.preventDefault();
            const tacheId = info.event.id.toString().replace('echeance-', '');
            openTacheModal(parseInt(tacheId));
        },
        editable: true,
        eventDrop: function(info) {
            // Quand une tâche est déplacée
            const tacheId = parseInt(info.event.id.toString().replace('echeance-', ''));
            const newDate = info.event.start.toISOString().split('T')[0];
            const eventType = info.event.extendedProps.type;
            
            // Ne permettre le déplacement que pour les événements planifiés
            if (eventType === 'echeance') {
                info.revert(); // Annuler le déplacement
                showNotification('Vous ne pouvez déplacer que la date de planification. Modifiez l\'échéance via le formulaire.', 'warning');
                return;
            }
            
            updateTacheDate(tacheId, newDate);
        },
        dateClick: function(info) {
            // Créer une nouvelle tâche à cette date
            openTacheModalWithDate(info.dateStr);
        }
    });
    
    calendarInstance.render();
}

/**
 * Retourne la couleur selon la priorité
 * @param {string} priorite - Priorité de la tâche
 * @returns {string} Couleur hex
 */
function getPriorityColor(priorite) {
    const colors = {
        'urgente': '#ef4444', // red-500
        'haute': '#f97316',   // orange-500
        'normale': '#3b82f6', // blue-500
        'basse': '#10b981'    // green-500
    };
    return colors[priorite] || colors['normale'];
}

/**
 * Retourne la couleur de planification selon le statut
 * @param {Object} tache - La tâche
 * @returns {string} Couleur hex
 */
function getPlanificationColor(tache) {
    const statut = tache.statut_planification;
    
    if (statut === 'en_retard') {
        return '#dc2626'; // red-600 - en retard
    } else if (statut === 'aujourdhui') {
        return '#2563eb'; // blue-600 - aujourd'hui
    } else {
        return getPriorityColor(tache.priorite); // couleur selon priorité pour à venir
    }
}

/**
 * Met à jour la date de planification d'une tâche
 * @param {number} tacheId - ID de la tâche
 * @param {string} newDate - Nouvelle date (YYYY-MM-DD)
 */
async function updateTacheDate(tacheId, newDate) {
    try {
        const response = await fetch(`/api/taches/${tacheId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ date_planifiee: newDate })
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Mettre à jour localement
            const tache = taches.find(t => t.id === tacheId);
            if (tache) {
                tache.date_planifiee = newDate;
            }
            
            showNotification('Date mise à jour', 'success');
        } else {
            showNotification('Erreur: ' + (result.error || 'Impossible de modifier la date'), 'error');
            // Recharger le calendrier
            loadTaches().then(() => initFullCalendar());
        }
    } catch (error) {
        console.error('Erreur:', error);
        showNotification('Erreur lors de la modification', 'error');
        loadTaches().then(() => initFullCalendar());
    }
}

/**
 * Ouvre le modal de tâche avec une date pré-remplie
 * @param {string} dateStr - Date au format YYYY-MM-DD
 */
function openTacheModalWithDate(dateStr) {
    openTacheModal();
    // Attendre que le modal soit ouvert
    setTimeout(() => {
        const dateInput = document.getElementById('tacheDateEcheance');
        if (dateInput) {
            dateInput.value = dateStr;
        }
    }, 100);
}

