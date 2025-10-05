# 📅 Module Planning - Documentation

## Vue d'ensemble

Le module de planning offre **3 vues différentes** pour visualiser et gérer vos tâches, plus un **système d'ordonnancement automatique**.

---

## 🎨 Les 3 vues

### 1. Vue Kanban 📊

**Tableau avec 4 colonnes drag & drop**

```
┌────────────┬────────────┬────────────┬────────────┐
│ 📝 À faire │ ⚙️ En cours│ ✅ Terminée│ ❌ Annulée │
│    (12)    │    (5)     │    (8)     │    (2)     │
├────────────┼────────────┼────────────┼────────────┤
│ [Tâche]    │ [Tâche]    │ [Tâche]    │ [Tâche]    │
│  drag →    │            │            │            │
└────────────┴────────────┴────────────┴────────────┘
```

**Fonctionnalités :**
- ✅ Drag & drop entre colonnes
- ✅ Mise à jour automatique du statut
- ✅ Compteurs par colonne
- ✅ Badges de priorité colorés
- ✅ Indication des échéances
- ✅ Édition rapide (clic sur l'icône)

**Technologies :**
- Sortable.js pour le drag & drop
- Synchronisation API temps réel

---

### 2. Vue Agenda 📅

**Calendrier mensuel/hebdomadaire**

**Fonctionnalités :**
- ✅ Calendrier mensuel, hebdomadaire, liste
- ✅ Tâches colorées par priorité
- ✅ Drag & drop pour changer les dates
- ✅ Création rapide (clic sur une date)
- ✅ Édition (clic sur une tâche)
- ✅ Vue mobile responsive

**Technologies :**
- FullCalendar 6.1.9
- Locale française
- API synchronisée

**Code couleurs :**
- 🔴 **Rouge** : Urgent
- 🟠 **Orange** : Haute
- 🔵 **Bleu** : Normale
- 🟢 **Vert** : Basse

---

### 3. Vue Liste 📝

**Liste détaillée avec tous les filtres**

Réutilise la vue Tâches existante avec :
- Tri par 12 critères
- Filtres multiples
- Recherche textuelle

---

## 🤖 Ordonnancement automatique

### Algorithme

Le système calcule un **score de priorité** pour chaque tâche basé sur :

| Critère | Poids | Détails |
|---------|-------|---------|
| **Priorité** | 40% | Urgente: 100 pts, Haute: 75, Normale: 50, Basse: 25 |
| **Échéance** | 40% | En retard: 100 pts, Aujourd'hui: 95, Demain: 85, etc. |
| **Statut** | 20% | En cours: 90 pts, À faire: 70 pts |

### Exemples de scores

```javascript
// Tâche urgente en retard
Priorité: 100 * 0.4 = 40
Échéance: 100 * 0.4 = 40  (en retard)
Statut: 90 * 0.2 = 18     (en cours)
Total: 98 pts

// Tâche normale dans 1 semaine
Priorité: 50 * 0.4 = 20
Échéance: 50 * 0.4 = 20
Statut: 70 * 0.2 = 14
Total: 54 pts
```

### Utilisation

1. Cliquez sur **"Ordonnancement auto"**
2. Consultez l'ordre suggéré avec justifications
3. Cliquez sur **"Appliquer cet ordre"**
4. Les tâches sont affichées dans l'ordre optimal

---

## 📂 Architecture des fichiers

```
public/js/
├── planning.js                  ← Module principal
└── planning/
    ├── kanban.js               ← Vue Kanban + drag & drop
    ├── agenda.js               ← Vue Agenda (FullCalendar)
    ├── scheduling.js           ← Ordonnancement automatique
    └── README.md               ← Cette doc
```

---

## 🔧 API utilisée

### Endpoints

| Méthode | Endpoint | Usage |
|---------|----------|-------|
| `GET` | `/api/taches` | Charger toutes les tâches |
| `GET` | `/api/taches/{id}` | Charger une tâche |
| `PUT` | `/api/taches/{id}` | Mettre à jour une tâche |

### Exemple de mise à jour (Kanban)

```javascript
// Quand une tâche est déplacée
fetch(`/api/taches/${tacheId}`, {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ statut: 'en_cours' })
});
```

### Exemple de mise à jour (Agenda)

```javascript
// Quand une tâche est déplacée dans le calendrier
fetch(`/api/taches/${tacheId}`, {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ date_echeance: '2025-10-10' })
});
```

---

## 🎯 Fonctions principales

### planning.js

| Fonction | Description |
|----------|-------------|
| `initPlanning()` | Initialise le module |
| `showPlanningView(view)` | Change de vue (kanban/agenda/liste) |
| `showPlanningTab()` | Affiche l'onglet Planning |
| `refreshPlanningView()` | Rafraîchit la vue courante |

### kanban.js

| Fonction | Description |
|----------|-------------|
| `renderKanbanView(container)` | Rend la vue Kanban |
| `renderKanbanColumn(colonne, taches)` | Rend une colonne |
| `renderKanbanCard(tache)` | Rend une carte |
| `initKanbanDragDrop()` | Initialise Sortable.js |
| `updateTacheStatus(id, status)` | Met à jour le statut |

### agenda.js

| Fonction | Description |
|----------|-------------|
| `renderAgendaView(container)` | Rend la vue Agenda |
| `initFullCalendar()` | Initialise FullCalendar |
| `getPriorityColor(priorite)` | Couleur selon priorité |
| `updateTacheDate(id, date)` | Met à jour la date |
| `openTacheModalWithDate(date)` | Crée une tâche à une date |

### scheduling.js

| Fonction | Description |
|----------|-------------|
| `calculateTaskPriority(tache)` | Calcule le score (0-100) |
| `suggestTaskOrder(taches)` | Génère l'ordre optimal |
| `getPriorityReason(tache)` | Explique le score |
| `showSchedulingModal()` | Affiche le modal |
| `applyScheduling()` | Applique l'ordre |

---

## 💡 Exemples d'utilisation

### Changer de vue programmatiquement

```javascript
showPlanningView('kanban');  // Vue Kanban
showPlanningView('agenda');  // Vue Agenda
showPlanningView('liste');   // Vue Liste
```

### Rafraîchir la vue

```javascript
// Après modification de données
loadTaches().then(() => {
    refreshPlanningView();
});
```

### Lancer l'ordonnancement

```javascript
showSchedulingModal();
```

---

## 🎨 Personnalisation CSS

### Classes principales

```css
/* Kanban */
.kanban-board { /* Grille principale */ }
.kanban-column { /* Colonne */ }
.kanban-card { /* Carte de tâche */ }
.kanban-ghost { /* Carte en drag (fantôme) */ }
.kanban-drag { /* Carte pendant le drag */ }

/* Agenda */
.fc { /* FullCalendar */ }
.fc-button { /* Boutons du calendrier */ }

/* Toolbar */
.view-btn { /* Boutons de vue */ }
.view-btn.active { /* Bouton actif */ }
```

### Personnaliser les couleurs Kanban

```css
.kanban-column {
    background-color: #f9fafb; /* Gris clair */
    border-radius: 0.5rem;
}

.kanban-card:hover {
    transform: translateY(-2px); /* Effet hover */
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
```

---

## 🚀 Performance

### Optimisations implémentées

1. **Lazy loading** - Les vues ne se chargent que quand nécessaires
2. **Instances réutilisables** - FullCalendar et Sortable sont réutilisés
3. **Mise à jour partielle** - Seules les données modifiées sont rechargées
4. **Debounce** - Les mises à jour API sont optimisées

### Limites recommandées

- **Kanban** : Jusqu'à 200 tâches
- **Agenda** : Jusqu'à 500 événements
- **Liste** : Pagination recommandée au-delà de 100 tâches

---

## 🐛 Troubleshooting

### La vue Kanban ne s'affiche pas

**Cause :** Sortable.js n'est pas chargé

**Solution :**
```html
<!-- Vérifier dans index.php -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
```

### Le calendrier ne s'affiche pas

**Cause :** FullCalendar n'est pas chargé

**Solution :**
```html
<!-- Vérifier dans index.php -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/locales/fr.global.min.js"></script>
```

### Le drag & drop ne fonctionne pas

**Cause :** Sortable n'est pas initialisé correctement

**Solution :**
```javascript
// Réinitialiser les instances
sortableInstances.forEach(instance => instance.destroy());
sortableInstances = [];
initKanbanDragDrop();
```

### L'ordonnancement ne suggère rien

**Cause :** Aucune tâche active (toutes terminées/annulées)

**Solution :** Vérifier que des tâches ont le statut "à faire" ou "en cours"

---

## 🔐 Sécurité

### Validation côté serveur

Toutes les mises à jour passent par l'API PHP qui valide :
- ✅ Format des données
- ✅ Valeurs autorisées (statuts, dates)
- ✅ Existence des entités

### Prévention XSS

- Échappement automatique dans les templates
- Sanitization des entrées utilisateur

---

## 📱 Mobile

### Responsiveness

Toutes les vues sont adaptées mobile :

- **Kanban** : Colonnes empilées verticalement
- **Agenda** : Vue jour par défaut sur mobile
- **Liste** : Cartes empilées

### Touch events

- ✅ Drag & drop tactile (Sortable.js)
- ✅ Swipe dans le calendrier
- ✅ Pinch to zoom

---

## 🎓 Ressources

### Bibliothèques utilisées

- [Sortable.js](https://sortablejs.github.io/Sortable/) - Drag & drop
- [FullCalendar](https://fullcalendar.io/) - Calendrier
- [Tailwind CSS](https://tailwindcss.com/) - Styling
- [Font Awesome](https://fontawesome.com/) - Icônes

### Documentation externe

- [FullCalendar Docs](https://fullcalendar.io/docs)
- [Sortable.js Docs](https://github.com/SortableJS/Sortable#options)

---

**Version :** 1.0.0  
**Dernière mise à jour :** Octobre 2025

