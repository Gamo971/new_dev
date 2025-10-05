# JavaScript Modules - Gestionnaire de Missions

Cette application utilise une architecture JavaScript modulaire pour une meilleure maintenabilité.

## Structure des fichiers

### 📁 `app.js` - Point d'entrée
- Initialise l'application au chargement du DOM
- Configure les écouteurs d'événements
- Charge les données initiales

### 📁 `utils.js` - Utilitaires
Fonctions de formatage et helpers :
- `formatDate(dateString)` - Formate les dates en français
- `formatDateTime(dateString)` - Formate les dates et heures
- `formatCurrency(amount)` - Formate les montants en euros
- `showNotification(message, type)` - Affiche des notifications toast

### 📁 `api.js` - Appels API
Gère toutes les communications avec l'API REST :
- `loadAllData()` - Charge toutes les données
- `loadMissions()`, `loadTaches()`, `loadClients()`, `loadContacts()` - Chargement par entité
- `loadStatistiques()` - Charge les statistiques globales
- `deleteMission()`, `deleteTache()`, `deleteClient()`, `deleteContact()` - Suppression

Variables globales :
- `missions[]`, `taches[]`, `clients[]`, `contacts[]`

### 📁 `ui.js` - Interface utilisateur
Gestion de l'affichage et du rendu HTML :
- `showTab(tabName)` - Affiche un onglet spécifique
- `displayMissions()`, `displayTaches()`, `displayClients()`, `displayContacts()` - Rendu des listes

### 📁 `filters.js` - Filtrage et tri
Gestion des filtres et du tri des données :
- `setupEventListeners()` - Configure les écouteurs pour les filtres
- `filterMissions()`, `filterTaches()`, `filterClients()`, `filterContacts()` - Filtrage
- `sortTaches(tachesToSort)` - Tri des tâches (12 critères disponibles)
- `toggleTacheFilters()` - Toggle des filtres avancés

### 📁 `modals.js` - Gestion des modals
Modals de création et édition :
- `openMissionModal()`, `openTacheModal()`, `openClientModal()`, `openContactModal()` - Ouverture
- `closeMissionModal()`, etc. - Fermeture
- `loadMissionData()`, etc. - Chargement des données pour édition
- `saveMission()`, `saveTache()`, `saveClient()`, `saveContact()` - Sauvegarde
- `loadClientsForSelect()`, `loadMissionsForSelect()` - Chargement des listes déroulantes

## Ordre de chargement

Les fichiers doivent être chargés dans cet ordre (déjà configuré dans `index.php`) :

1. `utils.js` - Utilitaires de base
2. `api.js` - Fonctions API et variables globales
3. `ui.js` - Affichage (utilise utils et variables d'api)
4. `filters.js` - Filtres (utilise api et ui)
5. `modals.js` - Modals (utilise api, ui et utils)
6. `app.js` - Initialisation (utilise tous les modules)

## Conventions

- ✅ Toutes les fonctions sont documentées avec des commentaires JSDoc
- ✅ Noms de fonctions en camelCase
- ✅ Constantes et variables claires
- ✅ Gestion d'erreurs avec try/catch
- ✅ Async/await pour les appels API
- ✅ Notifications utilisateur pour les actions

## Améliorations futures possibles

- [ ] Conversion en modules ES6 (import/export)
- [ ] Ajout de tests unitaires (Jest)
- [ ] Bundling avec Webpack/Vite
- [ ] TypeScript pour le typage statique
- [ ] State management (Redux, Vuex, etc.)

