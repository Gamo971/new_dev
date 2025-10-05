/**
 * Application principale - Point d'entrée
 * Gestionnaire de Missions - Cabinet Jarry
 */

// Initialisation au chargement du DOM
document.addEventListener('DOMContentLoaded', function() {
    // Charger toutes les données
    loadAllData();
    
    // Configurer les écouteurs d'événements pour les filtres
    setupEventListeners();
});

