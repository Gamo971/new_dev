/**
 * Gestionnaire de Capacité de Travail
 * Évite la surcharge en répartissant les tâches selon la disponibilité
 */

/**
 * Calcule la charge de travail par jour pour toutes les tâches planifiées
 * @returns {Object} Map {date: minutes_totales}
 */
async function getChargeParJour() {
    const chargeMap = {};
    
    try {
        // Récupérer toutes les tâches depuis le cache ou l'API
        let taches = window.allTaches || [];
        
        if (taches.length === 0) {
            const response = await fetch('/api/taches');
            const result = await response.json();
            if (result.success) {
                taches = result.data;
            }
        }
        
        // Calculer la charge par jour
        taches.forEach(tache => {
            // Ignorer les tâches terminées ou annulées
            if (tache.statut === 'terminee' || tache.statut === 'annulee') {
                return;
            }
            
            // Si la tâche a une date de planification
            if (tache.date_planifiee) {
                const date = tache.date_planifiee;
                const duree = parseInt(tache.temps_estime) || 0;
                
                if (!chargeMap[date]) {
                    chargeMap[date] = 0;
                }
                chargeMap[date] += duree;
            }
        });
        
        return chargeMap;
        
    } catch (error) {
        console.error('Erreur calcul charge:', error);
        return {};
    }
}

/**
 * Calcule la capacité disponible pour une date donnée
 * @param {string} dateStr - Date au format YYYY-MM-DD
 * @param {Object} chargeMap - Map des charges par jour
 * @param {number} tacheIdAIgnorer - ID de la tâche à ignorer (pour modification)
 * @returns {number} Minutes disponibles (peut être négatif si surchargé)
 */
function getCapaciteDisponible(dateStr, chargeMap, tacheIdAIgnorer = null) {
    const date = new Date(dateStr);
    
    // Vérifier que c'est un jour ouvré
    if (!isJourOuvre(date)) {
        return 0; // Pas de capacité les jours non travaillés
    }
    
    // Capacité maximale par jour (en minutes)
    const heuresTravailJour = window.getHeuresTravailParJour ? getHeuresTravailParJour() : 7;
    const capaciteMax = heuresTravailJour * 60; // Convertir en minutes
    
    // Charge déjà planifiée pour ce jour
    const chargeActuelle = chargeMap[dateStr] || 0;
    
    // Capacité disponible
    return capaciteMax - chargeActuelle;
}

/**
 * Trouve le prochain jour ouvré avec suffisamment de capacité
 * @param {Date} dateDebut - Date de départ
 * @param {number} dureeRequise - Durée nécessaire en minutes
 * @param {Object} chargeMap - Map des charges par jour
 * @returns {Date} Prochain jour disponible
 */
function getProchainJourDisponible(dateDebut, dureeRequise, chargeMap) {
    const date = new Date(dateDebut);
    date.setHours(0, 0, 0, 0);
    
    let tentatives = 0;
    const maxTentatives = 60; // Chercher jusqu'à 60 jours
    
    while (tentatives < maxTentatives) {
        const dateStr = date.toISOString().split('T')[0];
        
        // Vérifier si c'est un jour ouvré
        if (isJourOuvre(date)) {
            const capaciteDisponible = getCapaciteDisponible(dateStr, chargeMap);
            
            // Si assez de capacité pour la tâche
            if (capaciteDisponible >= dureeRequise) {
                return new Date(date);
            }
        }
        
        // Passer au jour suivant
        date.setDate(date.getDate() + 1);
        tentatives++;
    }
    
    // Si aucun jour trouvé (cas extrême), retourner la date initiale
    console.warn('Aucun jour avec capacité suffisante trouvé dans les 60 prochains jours');
    return new Date(dateDebut);
}

/**
 * Planification intelligente avec gestion de capacité
 * @param {Object} tache - Tâche à planifier
 * @param {Object} chargeMap - Map des charges (optionnel, sera calculé si absent)
 * @returns {string} Date planifiée au format YYYY-MM-DD
 */
async function calculateSmartSchedulingWithCapacity(tache, chargeMap = null) {
    // Si pas de charge map fournie, la calculer
    if (!chargeMap) {
        chargeMap = await getChargeParJour();
    }
    
    // Calculer la date de base avec l'algorithme existant
    const dateBase = calculateSmartScheduling(tache);
    
    if (!dateBase) {
        return null;
    }
    
    // Durée de la tâche en minutes
    const dureeRequise = parseInt(tache.temps_estime) || 0;
    
    // Si pas de temps estimé, retourner la date de base
    if (dureeRequise === 0) {
        return dateBase;
    }
    
    // Vérifier la capacité disponible pour cette date
    const dateBaseObj = new Date(dateBase);
    const capaciteDisponible = getCapaciteDisponible(dateBase, chargeMap, tache.id);
    
    // Si assez de capacité, retourner cette date
    if (capaciteDisponible >= dureeRequise) {
        return dateBase;
    }
    
    // Sinon, trouver le prochain jour disponible
    const prochainJourDispo = getProchainJourDisponible(dateBaseObj, dureeRequise, chargeMap);
    
    // Vérifier qu'on ne dépasse pas l'échéance
    if (tache.date_echeance) {
        const echeance = new Date(tache.date_echeance);
        if (prochainJourDispo > echeance) {
            // Alerte : impossible de planifier sans dépasser l'échéance
            console.warn(`Tâche "${tache.nom}" : capacité insuffisante avant échéance`);
            // Retourner quand même le jour disponible (utilisateur sera alerté)
        }
    }
    
    return prochainJourDispo.toISOString().split('T')[0];
}

/**
 * Re-planifie une tâche avec gestion de capacité
 * @param {Object} tache - Tâche à re-planifier
 * @returns {string|null} Nouvelle date planifiée
 */
async function rescheduleTacheWithCapacity(tache) {
    const chargeMap = await getChargeParJour();
    return await calculateSmartSchedulingWithCapacity(tache, chargeMap);
}

/**
 * Obtient des statistiques de charge pour une période
 * @param {string} dateDebut - Date de début YYYY-MM-DD
 * @param {string} dateFin - Date de fin YYYY-MM-DD
 * @returns {Array} Tableau de {date, charge, capacite, pourcentage}
 */
async function getStatistiquesCharge(dateDebut, dateFin) {
    const chargeMap = await getChargeParJour();
    const stats = [];
    
    const heuresTravailJour = window.getHeuresTravailParJour ? getHeuresTravailParJour() : 7;
    const capaciteMax = heuresTravailJour * 60;
    
    const debut = new Date(dateDebut);
    const fin = new Date(dateFin);
    const current = new Date(debut);
    
    while (current <= fin) {
        const dateStr = current.toISOString().split('T')[0];
        
        if (isJourOuvre(current)) {
            const charge = chargeMap[dateStr] || 0;
            const pourcentage = (charge / capaciteMax) * 100;
            
            stats.push({
                date: dateStr,
                charge: charge,
                capacite: capaciteMax,
                disponible: capaciteMax - charge,
                pourcentage: Math.round(pourcentage),
                surchargee: pourcentage > 100
            });
        }
        
        current.setDate(current.getDate() + 1);
    }
    
    return stats;
}

/**
 * Obtient la couleur selon le niveau de charge
 * @param {number} pourcentage - Pourcentage de charge (0-100+)
 * @returns {string} Classe CSS Tailwind
 */
function getChargeColor(pourcentage) {
    if (pourcentage >= 100) return 'text-red-600 font-bold'; // Surchargé
    if (pourcentage >= 80) return 'text-orange-600 font-semibold'; // Presque plein
    if (pourcentage >= 50) return 'text-yellow-600'; // Mi-charge
    return 'text-green-600'; // Disponible
}

/**
 * Affiche un indicateur de charge pour une date
 * @param {string} date - Date YYYY-MM-DD
 * @param {Object} chargeMap - Map des charges
 * @returns {string} HTML de l'indicateur
 */
function renderChargeIndicator(date, chargeMap) {
    const charge = chargeMap[date] || 0;
    const heuresTravailJour = window.getHeuresTravailParJour ? getHeuresTravailParJour() : 7;
    const capaciteMax = heuresTravailJour * 60;
    const pourcentage = Math.round((charge / capaciteMax) * 100);
    const heures = (charge / 60).toFixed(1);
    
    const colorClass = getChargeColor(pourcentage);
    
    return `
        <div class="text-xs ${colorClass}" title="${heures}h planifiées / ${heuresTravailJour}h disponibles">
            ${pourcentage}% (${heures}h)
        </div>
    `;
}

