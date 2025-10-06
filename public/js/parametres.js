/**
 * Gestion des paramètres de disponibilité
 */

// Variable globale pour stocker les paramètres
window.parametresData = window.parametresData || {};

/**
 * Charge tous les paramètres depuis l'API
 * @param {boolean} renderUI - Si true, affiche dans l'interface (défaut: true)
 */
async function loadParametres(renderUI = true) {
    try {
        const response = await fetch('/api/parametres');
        const result = await response.json();

        if (!result.success) {
            throw new Error('Impossible de charger les paramètres');
        }

        window.parametresData = {};
        result.data.all.forEach(p => {
            window.parametresData[p.cle] = p;
        });

        // Rendre dans l'interface seulement si demandé et si les éléments existent
        if (renderUI && document.getElementById('jours-travail-container')) {
            renderParametres();
            updateRecapitulatif();
        }

    } catch (error) {
        console.error('Erreur chargement paramètres:', error);
        if (renderUI && window.showNotification) {
            showNotification('Erreur lors du chargement des paramètres', 'error');
        }
    }
}

/**
 * Affiche les paramètres dans l'interface
 */
function renderParametres() {
    // Jours de travail
    renderJoursTravail();

    // Horaires
    document.getElementById('horaire_debut').value = window.parametresData['horaire_debut']?.valeur || '09:00';
    document.getElementById('horaire_fin').value = window.parametresData['horaire_fin']?.valeur || '18:00';
    document.getElementById('horaire_pause_duree').value = window.parametresData['horaire_pause_duree']?.valeur || '60';

    // Capacité
    document.getElementById('heures_travail_par_jour').value = window.parametresData['heures_travail_par_jour']?.valeur || '7';
    document.getElementById('heures_travail_par_semaine').value = window.parametresData['heures_travail_par_semaine']?.valeur || '35';

    // Planification
    const bufferValue = window.parametresData['buffer_planification']?.valeur || '0.2';
    document.getElementById('buffer_planification').value = parseFloat(bufferValue) * 100;
    document.getElementById('planification_auto_enabled').checked = window.parametresData['planification_auto_enabled']?.valeur === '1';

    // Ajouter les écouteurs d'événements
    addParametresEventListeners();
}

/**
 * Affiche les jours de travail
 */
function renderJoursTravail() {
    const container = document.getElementById('jours-travail-container');
    const jours = [
        { cle: 'lundi', label: 'Lundi', emoji: '📅' },
        { cle: 'mardi', label: 'Mardi', emoji: '📅' },
        { cle: 'mercredi', label: 'Mercredi', emoji: '📅' },
        { cle: 'jeudi', label: 'Jeudi', emoji: '📅' },
        { cle: 'vendredi', label: 'Vendredi', emoji: '📅' },
        { cle: 'samedi', label: 'Samedi', emoji: '📅' },
        { cle: 'dimanche', label: 'Dimanche', emoji: '🎉' }
    ];

    container.innerHTML = jours.map(jour => {
        const paramCle = `jours_travail_${jour.cle}`;
        const isChecked = window.parametresData[paramCle]?.valeur === '1';
        const activeClass = isChecked ? 'bg-green-100 border-green-500' : 'bg-gray-50 border-gray-300';

        return `
            <label class="flex items-center gap-2 p-3 border-2 rounded-lg cursor-pointer transition-all hover:shadow-md ${activeClass}">
                <input type="checkbox" 
                       id="${paramCle}" 
                       name="${paramCle}" 
                       ${isChecked ? 'checked' : ''}
                       onchange="updateJourStyle(this); updateRecapitulatif();"
                       class="w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500">
                <div class="flex flex-col">
                    <span class="text-sm font-medium">${jour.emoji} ${jour.label}</span>
                </div>
            </label>
        `;
    }).join('');
}

/**
 * Met à jour le style visuel d'un jour
 */
function updateJourStyle(checkbox) {
    const label = checkbox.closest('label');
    if (checkbox.checked) {
        label.classList.remove('bg-gray-50', 'border-gray-300');
        label.classList.add('bg-green-100', 'border-green-500');
    } else {
        label.classList.remove('bg-green-100', 'border-green-500');
        label.classList.add('bg-gray-50', 'border-gray-300');
    }
}

/**
 * Ajoute les écouteurs d'événements
 */
function addParametresEventListeners() {
    // Mettre à jour le récapitulatif quand on change un champ
    const fields = [
        'horaire_debut', 'horaire_fin', 'horaire_pause_duree',
        'heures_travail_par_jour', 'heures_travail_par_semaine',
        'buffer_planification', 'planification_auto_enabled'
    ];

    fields.forEach(field => {
        const element = document.getElementById(field);
        if (element) {
            element.addEventListener('change', updateRecapitulatif);
        }
    });
}

/**
 * Met à jour le récapitulatif
 */
function updateRecapitulatif() {
    const container = document.getElementById('parametres-recap');
    
    // Compter les jours de travail
    const joursChecked = document.querySelectorAll('input[id^="jours_travail_"]:checked').length;
    
    // Récupérer les valeurs
    const heuresJour = parseFloat(document.getElementById('heures_travail_par_jour').value) || 0;
    const heuresSemaine = parseFloat(document.getElementById('heures_travail_par_semaine').value) || 0;
    const buffer = parseFloat(document.getElementById('buffer_planification').value) || 0;
    
    // Calculer
    const capaciteSemaine = joursChecked * heuresJour;
    const difference = Math.abs(capaciteSemaine - heuresSemaine);
    
    let html = `
        <div class="space-y-2">
            <div class="flex justify-between">
                <span>🗓️ Jours travaillés :</span>
                <span class="font-semibold">${joursChecked} jour(s) / semaine</span>
            </div>
            <div class="flex justify-between">
                <span>⏰ Capacité calculée :</span>
                <span class="font-semibold">${capaciteSemaine.toFixed(1)}h / semaine</span>
            </div>
            <div class="flex justify-between">
                <span>📊 Capacité déclarée :</span>
                <span class="font-semibold">${heuresSemaine}h / semaine</span>
            </div>
    `;
    
    if (Math.abs(capaciteSemaine - heuresSemaine) > 0.1) {
        const icon = capaciteSemaine > heuresSemaine ? '⚠️' : 'ℹ️';
        html += `
            <div class="pt-2 border-t border-blue-300">
                <span class="text-xs">${icon} Différence de ${difference.toFixed(1)}h détectée</span>
            </div>
        `;
    }
    
    html += `
            <div class="pt-2 border-t border-blue-300">
                <span>🛡️ Buffer de sécurité :</span>
                <span class="font-semibold">${buffer}%</span>
            </div>
        </div>
    `;
    
    container.innerHTML = html;
}

/**
 * Sauvegarde tous les paramètres
 */
async function saveParametres() {
    try {
        // Préparer les données à sauvegarder
        const data = {};
        
        // Jours de travail
        const jours = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];
        jours.forEach(jour => {
            const checkbox = document.getElementById(`jours_travail_${jour}`);
            if (checkbox) {
                data[`jours_travail_${jour}`] = checkbox.checked ? '1' : '0';
            }
        });

        // Horaires
        data['horaire_debut'] = document.getElementById('horaire_debut').value;
        data['horaire_fin'] = document.getElementById('horaire_fin').value;
        data['horaire_pause_duree'] = document.getElementById('horaire_pause_duree').value;

        // Capacité
        data['heures_travail_par_jour'] = document.getElementById('heures_travail_par_jour').value;
        data['heures_travail_par_semaine'] = document.getElementById('heures_travail_par_semaine').value;

        // Planification
        const bufferPercent = document.getElementById('buffer_planification').value;
        data['buffer_planification'] = (parseFloat(bufferPercent) / 100).toString();
        data['planification_auto_enabled'] = document.getElementById('planification_auto_enabled').checked ? '1' : '0';

        // Envoyer à l'API
        const response = await fetch('/api/parametres/batch', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || 'Erreur lors de la sauvegarde');
        }

        showNotification(`✅ ${result.message}`, 'success');
        
        // Recharger les paramètres
        await loadParametres();

    } catch (error) {
        console.error('Erreur sauvegarde paramètres:', error);
        showNotification('❌ Erreur lors de la sauvegarde des paramètres', 'error');
    }
}

/**
 * Récupère les jours de travail
 * @returns {Array} Liste des jours travaillés
 */
function getJoursTravail() {
    const jours = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];
    return jours.filter(jour => {
        const cle = `jours_travail_${jour}`;
        return window.parametresData[cle]?.valeur === '1';
    });
}

/**
 * Récupère le nombre d'heures de travail par jour
 * @returns {number}
 */
function getHeuresTravailParJour() {
    return parseFloat(window.parametresData['heures_travail_par_jour']?.valeur || 7);
}

/**
 * Récupère le buffer de planification
 * @returns {number}
 */
function getBufferPlanification() {
    return parseFloat(window.parametresData['buffer_planification']?.valeur || 0.2);
}

/**
 * Vérifie si un jour est travaillé
 * @param {Date} date
 * @returns {boolean}
 */
function isJourTravaille(date) {
    const joursSemaine = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
    const jourName = joursSemaine[date.getDay()];
    const cle = `jours_travail_${jourName}`;
    return window.parametresData[cle]?.valeur === '1';
}

