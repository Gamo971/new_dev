/**
 * Application principale - Point d'entrée
 * Gestionnaire de Missions - Cabinet Jarry
 */

// Initialisation au chargement du DOM
document.addEventListener('DOMContentLoaded', async function() {
    // Charger les paramètres en premier (nécessaire pour la planification)
    // Ne pas rendre l'UI (renderUI = false), juste charger les données
    if (window.loadParametres) {
        try {
            await loadParametres(false);
            console.log('✅ Paramètres chargés au démarrage');
        } catch (error) {
            console.warn('⚠️ Erreur chargement paramètres:', error);
            // Continuer même si erreur (valeurs par défaut seront utilisées)
        }
    }
    
    // Charger toutes les données
    loadAllData();
    
    // Configurer les écouteurs d'événements pour les filtres
    setupEventListeners();
});

