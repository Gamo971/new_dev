/**
 * Ordonnancement automatique - Algorithme de priorisation
 */

/**
 * Calcule le score de priorité d'une tâche
 * @param {Object} tache - Tâche à évaluer
 * @returns {number} Score de 0 à 100
 */
function calculateTaskPriority(tache) {
    let score = 0;
    
    // 1. Priorité intrinsèque (40%)
    const prioriteScores = {
        'urgente': 100,
        'haute': 75,
        'normale': 50,
        'basse': 25
    };
    score += (prioriteScores[tache.priorite] || 50) * 0.4;
    
    // 2. Urgence temporelle (40%)
    if (tache.date_echeance) {
        const joursRestants = daysBetween(new Date(), new Date(tache.date_echeance));
        
        if (joursRestants < 0) {
            score += 100 * 0.4; // En retard = très urgent
        } else if (joursRestants === 0) {
            score += 95 * 0.4; // Aujourd'hui
        } else if (joursRestants === 1) {
            score += 85 * 0.4; // Demain
        } else if (joursRestants <= 3) {
            score += 70 * 0.4; // Cette semaine
        } else if (joursRestants <= 7) {
            score += 50 * 0.4; // Semaine prochaine
        } else {
            score += 30 * 0.4; // Plus lointain
        }
    } else {
        score += 20 * 0.4; // Pas d'échéance = moins prioritaire
    }
    
    // 3. Statut (20%)
    const statutScores = {
        'en_cours': 90,    // En cours = à terminer en priorité
        'a_faire': 70,     // À faire
        'terminee': 0,     // Déjà terminée
        'annulee': 0       // Annulée
    };
    score += (statutScores[tache.statut] || 0) * 0.2;
    
    return Math.round(score);
}

/**
 * Calcule le nombre de jours entre deux dates
 * @param {Date} date1 - Première date
 * @param {Date} date2 - Deuxième date
 * @returns {number} Nombre de jours
 */
function daysBetween(date1, date2) {
    const oneDay = 24 * 60 * 60 * 1000;
    date1 = new Date(date1.getFullYear(), date1.getMonth(), date1.getDate());
    date2 = new Date(date2.getFullYear(), date2.getMonth(), date2.getDate());
    return Math.round((date2 - date1) / oneDay);
}

/**
 * Génère un ordre suggéré pour les tâches
 * @param {Array} taches - Liste des tâches
 * @returns {Array} Tâches triées avec scores
 */
function suggestTaskOrder(taches) {
    // Filtrer les tâches terminées et annulées
    const activeTaches = taches.filter(t => 
        t.statut === 'a_faire' || t.statut === 'en_cours'
    );
    
    // Calculer le score pour chaque tâche
    const tachesWithScores = activeTaches.map(tache => ({
        ...tache,
        priorityScore: calculateTaskPriority(tache),
        reason: getPriorityReason(tache)
    }));
    
    // Trier par score décroissant
    tachesWithScores.sort((a, b) => b.priorityScore - a.priorityScore);
    
    return tachesWithScores;
}

/**
 * Génère une explication pour le score de priorité
 * @param {Object} tache - Tâche à analyser
 * @returns {string} Explication
 */
function getPriorityReason(tache) {
    const reasons = [];
    
    // Priorité
    if (tache.priorite === 'urgente') {
        reasons.push('⚠️ Priorité urgente');
    } else if (tache.priorite === 'haute') {
        reasons.push('🔴 Priorité haute');
    }
    
    // Échéance
    if (tache.date_echeance) {
        const jours = daysBetween(new Date(), new Date(tache.date_echeance));
        if (jours < 0) {
            reasons.push(`🚨 En retard de ${Math.abs(jours)} jour(s)`);
        } else if (jours === 0) {
            reasons.push('📅 Échéance aujourd\'hui');
        } else if (jours === 1) {
            reasons.push('📅 Échéance demain');
        } else if (jours <= 3) {
            reasons.push(`📅 Échéance dans ${jours} jours`);
        }
    }
    
    // Statut
    if (tache.statut === 'en_cours') {
        reasons.push('⚙️ Déjà en cours');
    }
    
    return reasons.length > 0 ? reasons.join(' • ') : 'Tâche normale';
}

/**
 * Affiche le modal d'ordonnancement
 */
function showSchedulingModal() {
    const orderedTasks = suggestTaskOrder(taches);
    
    if (orderedTasks.length === 0) {
        showNotification('Aucune tâche active à ordonnancer', 'info');
        return;
    }
    
    const modalHTML = `
        <div id="schedulingModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full max-h-[80vh] overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-gray-800">
                            <i class="fas fa-wand-magic-sparkles mr-2 text-purple-600"></i>
                            Ordonnancement automatique
                        </h3>
                        <button onclick="closeSchedulingModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    <p class="text-sm text-gray-600 mt-2">
                        Voici l'ordre suggéré basé sur la priorité, l'échéance et le statut
                    </p>
                </div>
                
                <div class="p-6 overflow-y-auto max-h-[calc(80vh-200px)]">
                    <div class="space-y-3">
                        ${orderedTasks.map((tache, index) => `
                            <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold">
                                        ${index + 1}
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2">
                                        <h4 class="font-semibold text-gray-800">${tache.nom}</h4>
                                        <div class="flex items-center gap-2 flex-shrink-0">
                                            ${Badge(tache.priorite_libelle, tache.priorite_couleur)}
                                            <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded text-sm font-medium">
                                                Score: ${tache.priorityScore}
                                            </span>
                                        </div>
                                    </div>
                                    ${tache.mission_nom ? `<p class="text-sm text-gray-600 mt-1">📁 ${tache.mission_nom}</p>` : ''}
                                    <p class="text-sm text-gray-600 mt-2">${tache.reason}</p>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
                
                <div class="p-6 border-t border-gray-200 bg-gray-50 flex justify-end gap-3">
                    <button onclick="closeSchedulingModal()" 
                            class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-100">
                        Fermer
                    </button>
                    <button onclick="applyScheduling()" 
                            class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                        <i class="fas fa-check mr-2"></i>Appliquer cet ordre
                    </button>
                </div>
            </div>
        </div>
    `;
    
    // Ajouter le modal au DOM
    const existingModal = document.getElementById('schedulingModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    document.body.insertAdjacentHTML('beforeend', modalHTML);
}

/**
 * Ferme le modal d'ordonnancement
 */
function closeSchedulingModal() {
    const modal = document.getElementById('schedulingModal');
    if (modal) {
        modal.remove();
    }
}

/**
 * Applique l'ordonnancement suggéré
 */
function applyScheduling() {
    showNotification('Ordonnancement appliqué ! Les tâches sont maintenant triées par priorité.', 'success');
    closeSchedulingModal();
    
    // Afficher la vue liste triée
    currentTab = 'taches';
    showTab('taches');
    
    // Trier les tâches
    const ordered = suggestTaskOrder(taches);
    displayTaches(ordered);
}

