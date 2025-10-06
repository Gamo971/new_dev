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
 * Utilise l'algorithme intelligent avec gestion de capacité
 */
async function autoScheduleTask() {
    const dateEcheance = document.getElementById('tacheDateEcheance').value;
    const priorite = document.getElementById('tachePriorite').value;
    const tempsEstime = parseInt(document.getElementById('tacheTempsEstime').value) || 0;
    const tacheId = document.getElementById('tacheId').value || null;
    
    if (!dateEcheance) {
        showNotification('Veuillez d\'abord définir une date d\'échéance', 'warning');
        document.getElementById('tacheDateEcheance').focus();
        return;
    }
    
    // Créer un objet tâche temporaire
    const tacheTmp = {
        id: tacheId,
        nom: document.getElementById('tacheNom').value || 'Nouvelle tâche',
        date_echeance: dateEcheance,
        priorite: priorite,
        temps_estime: tempsEstime
    };
    
    // Utiliser l'algorithme intelligent avec gestion de capacité
    let dateStr;
    if (window.calculateSmartSchedulingWithCapacity) {
        dateStr = await calculateSmartSchedulingWithCapacity(tacheTmp);
    } else {
        // Fallback si capacity-manager pas chargé
        dateStr = calculateSmartScheduling(tacheTmp);
    }
    
    if (!dateStr) {
        showNotification('Impossible de calculer une date de planification', 'error');
        return;
    }
    
    // Mettre à jour le champ
    document.getElementById('tacheDatePlanifiee').value = dateStr;
    
    // Mettre à jour l'affichage de la marge
    updateMargeInfo();
    
    // Message informatif avec info capacité
    const datePlanifiee = new Date(dateStr);
    const echeance = new Date(dateEcheance);
    const joursOuvres = compterJoursOuvres(datePlanifiee, echeance);
    
    // Vérifier si on a du reporter à cause de la capacité
    const dateBaseSansCapacite = calculateSmartScheduling(tacheTmp);
    let message = `📅 Date planifiée : ${formatDate(dateStr)} (${joursOuvres} jour(s) ouvré(s) avant échéance)`;
    
    if (dateBaseSansCapacite !== dateStr && tempsEstime > 0) {
        message += ' ⚠️ Date ajustée selon la capacité disponible';
    }
    
    showNotification(message, 'success');
}

/**
 * Vérifie si une date est un jour ouvré selon les paramètres
 * @param {Date} date
 * @returns {boolean}
 */
function isJourOuvre(date) {
    // Si les paramètres ne sont pas encore chargés, tous les jours sauf dimanche
    if (!window.parametresData || Object.keys(window.parametresData).length === 0) {
        return date.getDay() !== 0; // Pas le dimanche par défaut
    }
    
    const joursSemaine = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
    const jourName = joursSemaine[date.getDay()];
    const cle = `jours_travail_${jourName}`;
    
    return parametresData[cle]?.valeur === '1';
}

/**
 * Trouve le prochain jour ouvré à partir d'une date
 * @param {Date} date
 * @returns {Date}
 */
function getProchainJourOuvre(date) {
    const result = new Date(date);
    let tentatives = 0;
    
    // Chercher jusqu'à 14 jours maximum (éviter boucle infinie)
    while (!isJourOuvre(result) && tentatives < 14) {
        result.setDate(result.getDate() + 1);
        tentatives++;
    }
    
    return result;
}

/**
 * Calcule le nombre de jours ouvrés entre deux dates
 * @param {Date} dateDebut
 * @param {Date} dateFin
 * @returns {number}
 */
function compterJoursOuvres(dateDebut, dateFin) {
    let count = 0;
    const current = new Date(dateDebut);
    
    while (current <= dateFin) {
        if (isJourOuvre(current)) {
            count++;
        }
        current.setDate(current.getDate() + 1);
    }
    
    return count;
}

/**
 * Recule d'un certain nombre de jours ouvrés à partir d'une date
 * @param {Date} date
 * @param {number} joursOuvres
 * @returns {Date}
 */
function reculerJoursOuvres(date, joursOuvres) {
    const result = new Date(date);
    let count = 0;
    
    while (count < joursOuvres) {
        result.setDate(result.getDate() - 1);
        if (isJourOuvre(result)) {
            count++;
        }
    }
    
    return result;
}

/**
 * Calcule une suggestion de planification intelligente pour une tâche
 * Prend en compte les jours et horaires de travail définis dans les paramètres
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
    
    // Récupérer les paramètres de disponibilité
    const heuresTravailJour = window.getHeuresTravailParJour ? getHeuresTravailParJour() : 7;
    const bufferPlanif = window.getBufferPlanification ? getBufferPlanification() : 0.2;
    
    // Marge selon priorité (en jours ouvrés)
    const marges = {
        'urgente': 1,
        'haute': 2,
        'normale': 3,
        'basse': 5
    };
    
    let joursOuvresAvance = marges[tache.priorite] || 3;
    
    // Ajuster selon temps estimé et heures de travail par jour
    const heuresEstimees = (tache.temps_estime || 0) / 60;
    if (heuresEstimees > 0) {
        // Appliquer le buffer de sécurité
        const heuresAvecBuffer = heuresEstimees * (1 + bufferPlanif);
        
        // Calculer le nombre de jours ouvrés nécessaires
        const joursNecessaires = Math.ceil(heuresAvecBuffer / heuresTravailJour);
        
        // Prendre le maximum entre la marge de priorité et les jours nécessaires
        joursOuvresAvance = Math.max(joursOuvresAvance, joursNecessaires);
    }
    
    // Calculer la date de planification en reculant de N jours ouvrés
    let datePlanifiee = reculerJoursOuvres(echeance, joursOuvresAvance);
    
    // Ne pas planifier dans le passé
    if (datePlanifiee < aujourdhui) {
        // Planifier au prochain jour ouvré
        datePlanifiee = getProchainJourOuvre(aujourdhui);
    } else {
        // S'assurer que c'est un jour ouvré
        if (!isJourOuvre(datePlanifiee)) {
            datePlanifiee = getProchainJourOuvre(datePlanifiee);
        }
    }
    
    return datePlanifiee.toISOString().split('T')[0];
}

/**
 * Re-planifie automatiquement les tâches en retard
 * Gère 3 cas :
 * 1. Tâches non planifiées (sans date_planifiee)
 * 2. Tâches dont la date de planification est dépassée
 * 3. Tâches dont l'échéance est dépassée et non terminées
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
        
        // Trouver les tâches à re-planifier
        const tachesAReplanifier = taches.filter(t => {
            // Exclure les tâches terminées ou annulées
            if (t.statut === 'terminee' || t.statut === 'annulee') {
                return false;
            }
            
            // CAS 1 : Tâche non planifiée (avec échéance pour pouvoir calculer)
            if (!t.date_planifiee && t.date_echeance) {
                return true;
            }
            
            // CAS 2 : Date de planification dépassée
            if (t.date_planifiee && t.date_planifiee < aujourdhui) {
                return true;
            }
            
            // CAS 3 : Échéance dépassée et non terminée
            if (t.date_echeance && t.date_echeance < aujourdhui) {
                return true;
            }
            
            return false;
        });
        
        if (tachesAReplanifier.length === 0) {
            showNotification('✅ Toutes les tâches sont à jour !', 'success');
            return;
        }
        
        // Compter par catégorie pour le message
        const nonPlanifiees = tachesAReplanifier.filter(t => !t.date_planifiee).length;
        const planifRetard = tachesAReplanifier.filter(t => t.date_planifiee && t.date_planifiee < aujourdhui).length;
        const echeanceDepassee = tachesAReplanifier.filter(t => t.date_echeance && t.date_echeance < aujourdhui && !t.date_planifiee).length;
        
        // Message de confirmation détaillé
        let confirmMessage = `📋 Tâches à re-planifier : ${tachesAReplanifier.length}\n\n`;
        if (nonPlanifiees > 0) confirmMessage += `• ${nonPlanifiees} non planifiée(s)\n`;
        if (planifRetard > 0) confirmMessage += `• ${planifRetard} en retard de planification\n`;
        if (echeanceDepassee > 0) confirmMessage += `• ${echeanceDepassee} avec échéance dépassée\n`;
        confirmMessage += `\nVoulez-vous les re-planifier automatiquement ?`;
        
        if (!confirm(confirmMessage)) {
            return;
        }
        
        // Calculer la charge actuelle une fois pour toutes les tâches
        let chargeMap = {};
        if (window.getChargeParJour) {
            chargeMap = await getChargeParJour();
        }
        
        // Re-planifier chaque tâche en tenant compte de la capacité
        let replanifiees = 0;
        let erreurs = 0;
        let ajustements = 0; // Nombre de tâches dont la date a été ajustée pour la capacité
        
        for (const tache of tachesAReplanifier) {
            let nouvelleDatePlan;
            
            // Utiliser l'algorithme avec gestion de capacité si disponible
            if (window.calculateSmartSchedulingWithCapacity) {
                nouvelleDatePlan = await calculateSmartSchedulingWithCapacity(tache, chargeMap);
                
                // Vérifier si la date a été ajustée par rapport à la planification sans capacité
                const dateSansCapacite = calculateSmartScheduling(tache);
                if (nouvelleDatePlan !== dateSansCapacite && tache.temps_estime) {
                    ajustements++;
                }
                
                // Mettre à jour la charge map pour les prochaines tâches
                if (nouvelleDatePlan && tache.temps_estime) {
                    chargeMap[nouvelleDatePlan] = (chargeMap[nouvelleDatePlan] || 0) + parseInt(tache.temps_estime);
                }
            } else {
                nouvelleDatePlan = calculateSmartScheduling(tache);
            }
            
            if (nouvelleDatePlan) {
                try {
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
                    } else {
                        erreurs++;
                        console.error(`Erreur pour tâche ${tache.id}:`, await updateResponse.text());
                    }
                } catch (err) {
                    erreurs++;
                    console.error(`Erreur pour tâche ${tache.id}:`, err);
                }
            }
        }
        
        // Message de résultat
        let message = `✅ ${replanifiees} tâche(s) re-planifiée(s) avec succès !`;
        if (ajustements > 0) {
            message += ` (${ajustements} date(s) ajustée(s) selon la capacité)`;
        }
        if (erreurs > 0) {
            message += ` ⚠️ ${erreurs} erreur(s)`;
        }
        
        showNotification(message, replanifiees > 0 ? 'success' : 'warning');
        
        // Recharger les données
        await loadAllData();
        
        // Si on est sur l'onglet Planning, rafraîchir la vue
        if (document.getElementById('planning')?.classList.contains('active')) {
            const activeView = document.querySelector('.view-btn.active')?.dataset.view;
            if (activeView) {
                showPlanningView(activeView);
            }
        }
        
    } catch (error) {
        console.error('Erreur re-planification:', error);
        showNotification('❌ Erreur lors de la re-planification : ' + error.message, 'error');
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


