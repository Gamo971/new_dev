/**
 * Vue Agenda - Calendrier avec FullCalendar
 */

/**
 * Calcule les créneaux horaires pour les tâches d'un jour donné
 * @param {Array} tachesDuJour - Tâches planifiées ce jour
 * @param {string} date - Date au format YYYY-MM-DD
 * @returns {Array} Tâches avec start/end calculés
 */
function calculateTimeSlots(tachesDuJour, date) {
    // Récupérer les horaires de travail
    const horaireDebut = window.parametresData?.horaire_debut?.valeur || '09:00';
    const horaireFin = window.parametresData?.horaire_fin?.valeur || '18:00';
    const pauseDuree = parseInt(window.parametresData?.horaire_pause_duree?.valeur || 60);
    
    // Calculer l'heure de pause (milieu de journée par défaut)
    const debutMinutes = timeToMinutes(horaireDebut);
    const finMinutes = timeToMinutes(horaireFin);
    const milieuJournee = debutMinutes + (finMinutes - debutMinutes) / 2;
    const pauseDebut = minutesToTime(Math.floor(milieuJournee - pauseDuree / 2));
    const pauseFin = minutesToTime(Math.floor(milieuJournee + pauseDuree / 2));
    
    // Trier les tâches par ID (ordre de création)
    const tachesTriees = [...tachesDuJour].sort((a, b) => a.id - b.id);
    
    let heureActuelle = horaireDebut;
    const result = [];
    
    for (const tache of tachesTriees) {
        // Durée de la tâche (par défaut 30 min si non définie)
        const dureeMinutes = parseInt(tache.temps_estime) || 30;
        
        // Convertir heure actuelle en minutes
        let currentMinutes = timeToMinutes(heureActuelle);
        
        // Si on est dans la pause, sauter après la pause
        const pauseDebutMin = timeToMinutes(pauseDebut);
        const pauseFinMin = timeToMinutes(pauseFin);
        
        if (currentMinutes >= pauseDebutMin && currentMinutes < pauseFinMin) {
            currentMinutes = pauseFinMin;
        }
        
        // Calculer l'heure de fin de la tâche (sans pause d'abord)
        let endMinutes = currentMinutes + dureeMinutes;
        
        // Gestion de la pause : on ne l'ajoute que si la tâche est VRAIMENT coupée par la pause
        // C'est-à-dire : commence avant la pause ET se terminerait après la FIN de la pause (sans l'ajustement)
        if (currentMinutes < pauseDebutMin && endMinutes > pauseFinMin) {
            // La tâche est assez longue pour enjamber toute la pause
            // On ajoute la durée de la pause au temps d'affichage
            endMinutes = currentMinutes + dureeMinutes + pauseDuree;
        }
        // Sinon, on laisse la tâche se placer normalement, même si elle termine pendant la pause
        
        // Vérifier si on dépasse l'horaire de fin
        if (endMinutes > finMinutes) {
            // Reporter au lendemain
            const nextDate = getNextDate(date);
            result.push({
                ...tache,
                scheduledDate: nextDate,
                scheduledStart: horaireDebut,
                scheduledEnd: minutesToTime(timeToMinutes(horaireDebut) + dureeMinutes),
                overflow: true
            });
            // Ne pas mettre à jour heureActuelle, la prochaine tâche continue
            continue;
        }
        
        // Ajouter la tâche avec ses horaires
        result.push({
            ...tache,
            scheduledDate: date,
            scheduledStart: minutesToTime(currentMinutes),
            scheduledEnd: minutesToTime(endMinutes),
            overflow: false
        });
        
        // Mettre à jour l'heure actuelle pour la prochaine tâche
        heureActuelle = minutesToTime(endMinutes);
    }
    
    return result;
}

/**
 * Convertit une heure HH:MM en minutes
 * @param {string} time - Heure au format HH:MM
 * @returns {number} Minutes depuis minuit
 */
function timeToMinutes(time) {
    const [hours, minutes] = time.split(':').map(Number);
    return hours * 60 + minutes;
}

/**
 * Convertit des minutes en heure HH:MM
 * @param {number} minutes - Minutes depuis minuit
 * @returns {string} Heure au format HH:MM
 */
function minutesToTime(minutes) {
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    return `${String(hours).padStart(2, '0')}:${String(mins).padStart(2, '0')}`;
}

/**
 * Retourne la date du lendemain
 * @param {string} dateStr - Date au format YYYY-MM-DD
 * @returns {string} Date du lendemain
 */
function getNextDate(dateStr) {
    const date = new Date(dateStr);
    date.setDate(date.getDate() + 1);
    return date.toISOString().split('T')[0];
}

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
    
    // Grouper les tâches par date de planification
    const tachesParDate = {};
    
    taches.forEach(t => {
        if (t.date_planifiee && t.statut !== 'terminee' && t.statut !== 'annulee') {
            if (!tachesParDate[t.date_planifiee]) {
                tachesParDate[t.date_planifiee] = [];
            }
            tachesParDate[t.date_planifiee].push(t);
        }
    });
    
    // Calculer les créneaux horaires pour chaque jour
    const tachesAvecHoraires = [];
    
    Object.keys(tachesParDate).forEach(date => {
        const tachesDuJour = tachesParDate[date];
        const tachesCalculees = calculateTimeSlots(tachesDuJour, date);
        tachesAvecHoraires.push(...tachesCalculees);
    });
    
    // Mapper les tâches vers les événements FullCalendar
    const events = [];
    
    tachesAvecHoraires.forEach(t => {
        const startDateTime = `${t.scheduledDate}T${t.scheduledStart}:00`;
        const endDateTime = `${t.scheduledDate}T${t.scheduledEnd}:00`;
        
        // Emoji selon si débordement ou non
        const emoji = t.overflow ? '⚠️ ' : '📅 ';
        
        events.push({
            id: t.id,
            title: `${emoji}${t.nom}`,
            start: startDateTime,
            end: endDateTime,
            backgroundColor: t.overflow ? '#f59e0b' : getPlanificationColor(t), // Orange si débordement
            borderColor: t.planification_manuelle ? '#3b82f6' : '#10b981', // Bleu pour manuelle, vert pour automatique
            borderWidth: 2,
            extendedProps: {
                tache: t,
                type: 'planifiee',
                overflow: t.overflow
            }
        });
    });
    
    // Ajouter les échéances en arrière-plan
    taches.forEach(t => {
        if (t.date_echeance && t.statut !== 'terminee') {
            events.push({
                id: `echeance-${t.id}`,
                title: `🏁 ${t.nom} (échéance)`,
                start: t.date_echeance,
                allDay: true,
                backgroundColor: '#cbd5e1',
                borderColor: t.planification_manuelle ? '#3b82f6' : '#10b981', // Bleu pour manuelle, vert pour automatique
                display: 'background',
                extendedProps: {
                    tache: t,
                    type: 'echeance'
                }
            });
        }
    });
    
    // Récupérer les horaires de travail pour la configuration
    const horaireDebut = window.parametresData?.horaire_debut?.valeur || '09:00';
    const horaireFin = window.parametresData?.horaire_fin?.valeur || '18:00';
    
    // Créer le calendrier
    calendarInstance = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek', // Vue semaine par défaut
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
        // Heures de travail
        slotMinTime: horaireDebut,
        slotMaxTime: horaireFin,
        slotDuration: '00:30:00', // Créneaux de 30 minutes
        allDaySlot: true, // Garder la ligne all-day pour les échéances
        nowIndicator: true, // Ligne indiquant l'heure actuelle
        
        // Heures ouvrables (optionnel, pour colorer)
        businessHours: {
            daysOfWeek: [1, 2, 3, 4, 5], // Lundi à Vendredi (sera dynamique si besoin)
            startTime: horaireDebut,
            endTime: horaireFin
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
        eventResize: function(info) {
            // Quand on redimensionne une tâche (change la durée)
            const tacheId = parseInt(info.event.id);
            const start = info.event.start;
            const end = info.event.end;
            const durationMinutes = Math.round((end - start) / (1000 * 60));
            
            // Mettre à jour la durée de la tâche
            updateTacheDuration(tacheId, durationMinutes);
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
 * Met à jour la date et l'heure de planification d'une tâche
 * @param {number} tacheId - ID de la tâche
 * @param {string} newDate - Nouvelle date (YYYY-MM-DD)
 * @param {string} newTime - Nouvelle heure (HH:MM:SS)
 * @param {string} planificationType - Type de planification ('manuelle' ou 'automatique')
 */
async function updateTacheDate(tacheId, newDate, newTime, planificationType = 'automatique') {
    try {
        console.log(`Mise à jour de la tâche ${tacheId} avec la date ${newDate}, heure ${newTime}, type ${planificationType}`);
        
        const updateData = { 
            date_planifiee: newDate,
            heure_debut_planifiee: newTime,
            planification_type: planificationType
        };
        
        console.log('🔄 Mise à jour drag & drop:', updateData);
        
        const response = await fetch(`/api/taches/${tacheId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(updateData)
        });
        
        const result = await response.json();
        console.log('Résultat de la mise à jour:', result);
        
        if (result.success) {
            // Mettre à jour la tâche dans la variable globale taches
            const tacheIndex = taches.findIndex(t => t.id === tacheId);
            if (tacheIndex !== -1) {
                taches[tacheIndex].date_planifiee = newDate;
                taches[tacheIndex].heure_debut_planifiee = newTime;
                taches[tacheIndex].planification_type = planificationType;
                console.log(`✅ Tâche ${tacheId} mise à jour localement:`, {
                    date: newDate,
                    heure: newTime,
                    type: planificationType
                });
            } else {
                console.warn(`⚠️ Tâche ${tacheId} non trouvée dans la liste locale`);
            }
            
            showNotification('Date et heure de planification mises à jour avec succès', 'success');
        } else {
            showNotification('Erreur: ' + (result.error || 'Impossible de modifier la planification'), 'error');
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
 * Met à jour la durée estimée d'une tâche
 * @param {number} tacheId - ID de la tâche
 * @param {number} durationMinutes - Nouvelle durée en minutes
 */
async function updateTacheDuration(tacheId, durationMinutes) {
    try {
        const response = await fetch(`/api/taches/${tacheId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ temps_estime: durationMinutes })
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Mettre à jour localement
            const tache = taches.find(t => t.id === tacheId);
            if (tache) {
                tache.temps_estime = durationMinutes;
            }
            
            const heures = Math.floor(durationMinutes / 60);
            const minutes = durationMinutes % 60;
            showNotification(`Durée mise à jour : ${heures}h${minutes}min`, 'success');
        } else {
            showNotification('Erreur: ' + (result.error || 'Impossible de modifier la durée'), 'error');
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

