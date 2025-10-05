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
    const events = taches
        .filter(t => t.date_echeance) // Seulement les tâches avec échéance
        .map(t => ({
            id: t.id,
            title: t.nom,
            start: t.date_echeance,
            allDay: true,
            backgroundColor: getPriorityColor(t.priorite),
            borderColor: getPriorityColor(t.priorite),
            extendedProps: {
                tache: t
            }
        }));
    
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
            const tacheId = info.event.id;
            openTacheModal(parseInt(tacheId));
        },
        editable: true,
        eventDrop: function(info) {
            // Quand une tâche est déplacée
            const tacheId = parseInt(info.event.id);
            const newDate = info.event.start.toISOString().split('T')[0];
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
 * Met à jour la date d'échéance d'une tâche
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
            body: JSON.stringify({ date_echeance: newDate })
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Mettre à jour localement
            const tache = taches.find(t => t.id === tacheId);
            if (tache) {
                tache.date_echeance = newDate;
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

