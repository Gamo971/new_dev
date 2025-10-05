/**
 * Task Scheduler - Gestion de la planification automatique des tâches
 */

/**
 * Met à jour l'info sur la marge de sécurité entre date planifiée et échéance
 */
function updateMargeInfo() {
    const dateEcheance = document.getElementById('tacheDateEcheance').value;
    const datePlanifiee = document.getElementById('tacheDatePlanifiee').value;
    const tempsEstime = parseInt(document.getElementById('tacheTempsEstime').value) || 0;
    const margeInfo = document.getElementById('margeInfo');
    const margeText = document.getElementById('margeText');
    
    if (!dateEcheance || !datePlanifiee) {
        margeInfo.classList.add('hidden');
        return;
    }
    
    const echeance = new Date(dateEcheance);
    const planifiee = new Date(datePlanifiee);
    const diffTime = echeance - planifiee;
    const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
    
    margeInfo.classList.remove('hidden');
    
    if (diffDays < 0) {
        // Planifiée APRÈS l'échéance - DANGER
        margeInfo.className = 'mb-4 p-3 bg-red-50 border border-red-200 rounded-lg';
        margeText.className = 'text-sm text-red-700 font-medium';
        margeText.innerHTML = `<i class="fas fa-exclamation-triangle mr-1"></i><strong>Attention !</strong> La tâche est planifiée ${Math.abs(diffDays)} jour(s) APRÈS l'échéance. Risque de retard !`;
    } else if (diffDays === 0) {
        // Même jour
        margeInfo.className = 'mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg';
        margeText.className = 'text-sm text-yellow-700';
        margeText.innerHTML = `<i class="fas fa-exclamation mr-1"></i>Planifiée le jour même de l'échéance. Aucune marge de sécurité.`;
    } else if (diffDays === 1) {
        // 1 jour de marge
        margeInfo.className = 'mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg';
        margeText.className = 'text-sm text-yellow-700';
        margeText.innerHTML = `<i class="fas fa-clock mr-1"></i>Marge de sécurité : 1 jour. Marge minimale.`;
    } else if (diffDays <= 3) {
        // 2-3 jours de marge - OK
        margeInfo.className = 'mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg';
        margeText.className = 'text-sm text-blue-700';
        margeText.innerHTML = `<i class="fas fa-check-circle mr-1"></i>Marge de sécurité : ${diffDays} jours. Bonne planification.`;
    } else {
        // > 3 jours de marge - OPTIMAL
        margeInfo.className = 'mb-4 p-3 bg-green-50 border border-green-200 rounded-lg';
        margeText.className = 'text-sm text-green-700';
        margeText.innerHTML = `<i class="fas fa-thumbs-up mr-1"></i>Marge de sécurité : ${diffDays} jours. Excellente anticipation !`;
    }
    
    // Ajouter info temps estimé si renseigné
    if (tempsEstime > 0) {
        const heures = Math.floor(tempsEstime / 60);
        const minutes = tempsEstime % 60;
        let tempsStr = heures > 0 ? `${heures}h` : '';
        tempsStr += minutes > 0 ? `${minutes}min` : '';
        margeText.innerHTML += ` <span class="text-gray-600">(Temps estimé : ${tempsStr})</span>`;
    }
}

/**
 * Planification automatique basée sur l'échéance et la priorité
 */
function autoScheduleTask() {
    const dateEcheance = document.getElementById('tacheDateEcheance').value;
    const priorite = document.getElementById('tachePriorite').value;
    const tempsEstime = parseInt(document.getElementById('tacheTempsEstime').value) || 0;
    
    if (!dateEcheance) {
        showNotification('Veuillez d\'abord définir une date d\'échéance', 'warning');
        document.getElementById('tacheDateEcheance').focus();
        return;
    }
    
    const echeance = new Date(dateEcheance);
    const aujourdhui = new Date();
    aujourdhui.setHours(0, 0, 0, 0);
    
    // Calculer le nombre de jours de marge en fonction de la priorité
    let joursAvance;
    switch (priorite) {
        case 'urgente':
            joursAvance = 1; // 1 jour avant
            break;
        case 'haute':
            joursAvance = 3; // 3 jours avant
            break;
        case 'normale':
            joursAvance = 5; // 5 jours avant
            break;
        case 'basse':
            joursAvance = 7; // 7 jours avant
            break;
        default:
            joursAvance = 5;
    }
    
    // Ajuster en fonction du temps estimé
    const heuresEstimees = tempsEstime / 60;
    if (heuresEstimees > 8) {
        // Plus d'une journée de travail
        joursAvance += Math.ceil(heuresEstimees / 8);
    }
    
    // Calculer la date de planification suggérée
    const datePlanifiee = new Date(echeance);
    datePlanifiee.setDate(datePlanifiee.getDate() - joursAvance);
    
    // Ne pas planifier dans le passé
    if (datePlanifiee < aujourdhui) {
        datePlanifiee.setTime(aujourdhui.getTime());
    }
    
    // Mettre à jour le champ
    const dateStr = datePlanifiee.toISOString().split('T')[0];
    document.getElementById('tacheDatePlanifiee').value = dateStr;
    
    // Mettre à jour l'affichage de la marge
    updateMargeInfo();
    
    // Notification
    showNotification(`📅 Date planifiée suggérée : ${formatDate(dateStr)} (${joursAvance} jours avant l'échéance)`, 'success');
}

/**
 * Calcule une suggestion de planification intelligente pour une tâche
 * @param {Object} tache - Tâche à planifier
 * @returns {string} Date suggérée au format YYYY-MM-DD
 */
function calculateSmartScheduling(tache) {
    if (!tache.date_echeance) {
        return null;
    }
    
    const echeance = new Date(tache.date_echeance);
    const aujourdhui = new Date();
    aujourdhui.setHours(0, 0, 0, 0);
    
    // Marge selon priorité
    const marges = {
        'urgente': 1,
        'haute': 3,
        'normale': 5,
        'basse': 7
    };
    
    let joursAvance = marges[tache.priorite] || 5;
    
    // Ajuster selon temps estimé
    const heuresEstimees = (tache.temps_estime || 0) / 60;
    if (heuresEstimees > 8) {
        joursAvance += Math.ceil(heuresEstimees / 8);
    }
    
    const datePlanifiee = new Date(echeance);
    datePlanifiee.setDate(datePlanifiee.getDate() - joursAvance);
    
    // Ne pas planifier dans le passé
    if (datePlanifiee < aujourdhui) {
        return aujourdhui.toISOString().split('T')[0];
    }
    
    return datePlanifiee.toISOString().split('T')[0];
}

/**
 * Re-planifie automatiquement les tâches en retard
 */
async function rescheduleLateTasks() {
    try {
        // Charger toutes les tâches
        const response = await fetch('/api/taches');
        const result = await response.json();
        
        if (!result.success) {
            throw new Error('Impossible de charger les tâches');
        }
        
        const taches = result.data;
        const aujourdhui = new Date().toISOString().split('T')[0];
        
        // Trouver les tâches en retard de planification
        const tachesEnRetard = taches.filter(t => {
            return t.date_planifiee && 
                   t.date_planifiee < aujourdhui && 
                   t.statut !== 'terminee' && 
                   t.statut !== 'annulee';
        });
        
        if (tachesEnRetard.length === 0) {
            showNotification('Aucune tâche en retard de planification', 'info');
            return;
        }
        
        // Afficher modal de confirmation
        const confirmMessage = `${tachesEnRetard.length} tâche(s) en retard de planification détectée(s).\n\nVoulez-vous les re-planifier automatiquement ?`;
        
        if (!confirm(confirmMessage)) {
            return;
        }
        
        // Re-planifier chaque tâche
        let replanifiees = 0;
        for (const tache of tachesEnRetard) {
            const nouvelleDatePlan = calculateSmartScheduling(tache);
            
            if (nouvelleDatePlan) {
                // Mettre à jour la tâche
                const updateResponse = await fetch(`/api/taches/${tache.id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        ...tache,
                        date_planifiee: nouvelleDatePlan
                    })
                });
                
                if (updateResponse.ok) {
                    replanifiees++;
                }
            }
        }
        
        showNotification(`✅ ${replanifiees} tâche(s) re-planifiée(s) avec succès !`, 'success');
        
        // Recharger les données
        await loadAllData();
        
    } catch (error) {
        console.error('Erreur re-planification:', error);
        showNotification('Erreur lors de la re-planification : ' + error.message, 'error');
    }
}

/**
 * Retourne le badge HTML pour le statut de planification
 * @param {Object} tache - La tâche
 * @returns {string} HTML du badge
 */
function getPlanificationBadge(tache) {
    if (!tache.date_planifiee) {
        return '<span class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs">📋 Non planifiée</span>';
    }
    
    const statut = tache.statut_planification;
    
    switch (statut) {
        case 'en_retard':
            return '<span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs font-medium">⚠️ En retard</span>';
        case 'aujourdhui':
            return '<span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs font-medium">📅 Aujourd\'hui</span>';
        case 'a_venir':
            return '<span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs">✓ Planifiée</span>';
        case 'terminee':
            return '<span class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs">✓ Terminée</span>';
        default:
            return '';
    }
}

/**
 * Retourne l'info de marge pour affichage
 * @param {Object} tache - La tâche
 * @returns {string} HTML de l'info marge
 */
function getMargeInfo(tache) {
    if (!tache.date_planifiee || !tache.date_echeance) {
        return '';
    }
    
    const marge = tache.marge_avant_echeance;
    
    if (marge === null) {
        return '';
    }
    
    if (marge < 0) {
        return `<span class="text-red-600 text-xs">⚠️ ${Math.abs(marge)}j après échéance!</span>`;
    } else if (marge === 0) {
        return `<span class="text-yellow-600 text-xs">⚠️ Jour même</span>`;
    } else if (marge <= 2) {
        return `<span class="text-yellow-600 text-xs">⏰ ${marge}j avant</span>`;
    } else {
        return `<span class="text-green-600 text-xs">✓ ${marge}j avant</span>`;
    }
}

