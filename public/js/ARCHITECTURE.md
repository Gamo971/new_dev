# Architecture JavaScript - Diagramme de dépendances

## 🏗️ Vue d'ensemble

```
┌─────────────────────────────────────────────────┐
│                   index.php                      │
│  (HTML + Backend PHP + Chargement des scripts)  │
└─────────────────────────────────────────────────┘
                        │
                        ▼
        ┌───────────────────────────────┐
        │   Chargement des modules JS   │
        └───────────────────────────────┘
                        │
        ┌───────────────┴───────────────┐
        │                               │
        ▼                               ▼
┌──────────────┐               ┌──────────────┐
│  utils.js    │               │   api.js     │
│  (helpers)   │               │ (données)    │
└──────────────┘               └──────────────┘
        │                               │
        └───────────────┬───────────────┘
                        │
                        ▼
                ┌──────────────┐
                │    ui.js     │
                │ (affichage)  │
                └──────────────┘
                        │
        ┌───────────────┴───────────────┐
        │                               │
        ▼                               ▼
┌──────────────┐               ┌──────────────┐
│ filters.js   │               │  modals.js   │
│  (filtres)   │               │  (édition)   │
└──────────────┘               └──────────────┘
        │                               │
        └───────────────┬───────────────┘
                        │
                        ▼
                ┌──────────────┐
                │   app.js     │
                │    (init)    │
                └──────────────┘
```

## 📦 Dépendances entre modules

### utils.js (Aucune dépendance)
- Fonctions pures
- Utilisé par : `ui.js`, `modals.js`

### api.js (Dépend de : utils.js)
- Variables globales : `missions`, `taches`, `clients`, `contacts`
- Utilise : `showNotification()` de utils
- Utilisé par : tous les autres modules

### ui.js (Dépend de : utils.js, api.js)
- Utilise : variables globales d'api
- Utilise : fonctions de formatage d'utils
- Utilisé par : `filters.js`, `modals.js`

### filters.js (Dépend de : api.js, ui.js)
- Utilise : variables globales d'api
- Utilise : fonctions d'affichage d'ui
- Utilisé par : `app.js`

### modals.js (Dépend de : utils.js, api.js, ui.js)
- Utilise : `showNotification()` d'utils
- Utilise : fonctions de chargement d'api
- Utilisé par : UI (événements onclick)

### app.js (Dépend de : tous les modules)
- Point d'entrée
- Initialise l'application
- Configure les événements

## 🔄 Flux de données

### 1. Chargement initial
```
app.js (DOMContentLoaded)
  └─> loadAllData() [api.js]
       ├─> loadMissions() → displayMissions() [ui.js]
       ├─> loadTaches() → displayTaches() [ui.js]
       ├─> loadClients() → displayClients() [ui.js]
       └─> loadContacts() → displayContacts() [ui.js]
```

### 2. Navigation (Changement d'onglet)
```
User click sur onglet
  └─> showTab(tabName) [ui.js]
       └─> load[Entity]() [api.js]
            └─> display[Entity]() [ui.js]
```

### 3. Filtrage
```
User saisie dans recherche
  └─> filter[Entity]() [filters.js]
       ├─> Lecture des filtres
       ├─> Filtrage du tableau
       └─> display[Entity]() [ui.js]
```

### 4. Création/Édition
```
User click "Nouveau" ou "Modifier"
  └─> open[Entity]Modal() [modals.js]
       ├─> load[Entity]sForSelect() [modals.js]
       └─> (si édition) load[Entity]Data() [modals.js]

User click "Sauvegarder"
  └─> save[Entity](event) [modals.js]
       ├─> fetch API POST/PUT
       ├─> close[Entity]Modal()
       ├─> load[Entity]() [api.js]
       ├─> loadStatistiques() [api.js]
       └─> showNotification() [utils.js]
```

### 5. Suppression
```
User click "Supprimer"
  └─> delete[Entity](id) [api.js]
       ├─> confirm()
       ├─> fetch API DELETE
       ├─> load[Entity]() [api.js]
       ├─> loadStatistiques() [api.js]
       └─> showNotification() [utils.js]
```

## 🎨 Composants UI

### Onglets
- Missions (bleu) 💼
- Tâches (vert) ✅
- Clients (violet) 🏢
- Contacts (orange) 👤
- Statistiques (indigo) 📊

### Modals
Chaque entité a son modal avec :
- Titre dynamique (Nouveau/Modifier)
- Formulaire avec validation
- Boutons Annuler/Sauvegarder

### Filtres
- **Recherche textuelle** : Tous les onglets
- **Filtres dropdown** : Statut, Priorité
- **Filtres checkbox** : Tâches (multiples)
- **Tri** : 12 critères pour les tâches

## 📊 État de l'application

### Variables globales (dans api.js)
```javascript
let missions = [];    // Array des missions
let taches = [];      // Array des tâches
let clients = [];     // Array des clients
let contacts = [];    // Array des contacts
```

### Variable d'état UI (dans ui.js)
```javascript
let currentTab = 'missions';  // Onglet actif
```

## 🔐 Sécurité

### XSS Prevention
- Utilisation de template literals avec échappement automatique
- Validation côté serveur (API PHP)

### API
- Validation des entrées
- Gestion des erreurs
- Messages d'erreur utilisateur

## 🎯 Points d'extension

Pour ajouter une nouvelle entité :

1. **API** : Ajouter les fonctions CRUD dans `api.js`
2. **UI** : Ajouter la fonction d'affichage dans `ui.js`
3. **Filtres** : Ajouter la logique dans `filters.js`
4. **Modal** : Ajouter open/close/save dans `modals.js`
5. **HTML** : Ajouter l'onglet et le modal dans `index.php`

## 📝 Conventions de code

### Nommage
- **Variables** : camelCase (`missions`, `currentTab`)
- **Fonctions** : camelCase avec verbe (`loadMissions`, `displayTaches`)
- **Constantes** : UPPER_SNAKE_CASE (si ajoutées à l'avenir)

### Organisation des fonctions
1. Variables globales
2. Fonctions d'initialisation
3. Fonctions de chargement
4. Fonctions d'affichage
5. Fonctions utilitaires

### Documentation
- Commentaire d'en-tête de fichier
- Commentaire JSDoc pour chaque fonction
- Commentaires inline pour la logique complexe

---

**Dernière mise à jour** : Octobre 2025

