# 📅 Architecture du Module de Planning

## 🎯 Objectifs

Créer un système de visualisation et d'ordonnancement des tâches avec :

1. **Vues multiples** : Agenda, Liste, Kanban, Timeline (Gantt)
2. **Ordonnancement automatique** : Optimisation basée sur priorité, échéance, dépendances
3. **Interaction** : Drag & drop, édition rapide
4. **Temps réel** : Synchronisation avec l'API

---

## 🏗️ Architecture proposée

### Option 1 : Bibliothèques JavaScript (Recommandé)

#### Bibliothèques à utiliser

| Bibliothèque | Fonction | CDN | Taille |
|--------------|----------|-----|--------|
| **FullCalendar** | Vue Agenda/Timeline | ✅ | ~200KB |
| **Sortable.js** | Drag & drop Kanban | ✅ | ~45KB |
| **Vis.js Timeline** | Vue Gantt | ✅ | ~150KB |

**Total : ~395KB (minifié + gzippé : ~120KB)**

#### Avantages
✅ Rapide à implémenter  
✅ Fonctionnalités riches  
✅ Bien documentées  
✅ Support mobile  
✅ Pas de build nécessaire  

#### Inconvénients
❌ Dépendances externes  
❌ Personnalisation limitée  

---

### Option 2 : Implémentation sur mesure

#### Composants à créer

1. **Vue Agenda** - Calendrier mensuel/hebdomadaire
2. **Vue Kanban** - Colonnes de statuts
3. **Vue Timeline** - Diagramme de Gantt
4. **Vue Liste** - Liste triable

#### Avantages
✅ Contrôle total  
✅ Pas de dépendances  
✅ Personnalisation maximale  

#### Inconvénients
❌ Temps de développement (4-6 semaines)  
❌ Maintenance complexe  
❌ Réinvention de la roue  

---

## 🎨 Vues proposées

### 1. Vue Agenda (Calendrier)

**Fonctionnalités :**
- Calendrier mensuel avec tâches par jour
- Vue semaine avec horaires
- Vue jour détaillée
- Drag & drop pour déplacer les tâches
- Création rapide par clic

**Bibliothèque : FullCalendar**
```html
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.js"></script>
```

**Exemple :**
```javascript
const calendar = new FullCalendar.Calendar(element, {
    initialView: 'dayGridMonth',
    events: taches.map(t => ({
        id: t.id,
        title: t.nom,
        start: t.date_echeance,
        color: getPriorityColor(t.priorite)
    })),
    eventClick: (info) => openTacheModal(info.event.id)
});
```

---

### 2. Vue Kanban (Tableau)

**Colonnes :**
- 📝 À faire
- ⚙️ En cours
- ✅ Terminée
- ❌ Annulée

**Fonctionnalités :**
- Drag & drop entre colonnes
- Compteur de tâches par colonne
- Filtrage par priorité
- Badges colorés

**Bibliothèque : Sortable.js**
```html
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
```

**Structure :**
```html
<div class="kanban-board">
    <div class="kanban-column" data-status="a_faire">
        <h3>📝 À faire (5)</h3>
        <div class="kanban-tasks" id="tasks-a-faire">
            <!-- Tâches -->
        </div>
    </div>
    <!-- Autres colonnes -->
</div>
```

---

### 3. Vue Timeline (Gantt)

**Fonctionnalités :**
- Visualisation temporelle des tâches
- Barres de progression
- Dépendances entre tâches
- Jalons (milestones)
- Zoom temporel (jour, semaine, mois)

**Bibliothèque : Vis.js Timeline**
```html
<script src="https://unpkg.com/vis-timeline@7.7.2/standalone/umd/vis-timeline-graph2d.min.js"></script>
```

---

### 4. Vue Liste (Existante améliorée)

**Améliorations :**
- Tri par date d'échéance
- Groupement par mission
- Progression visuelle
- Actions rapides

---

## 🤖 Ordonnancement Automatique

### Algorithme proposé

#### Critères de priorité (pondérés)

```javascript
function calculateTaskPriority(tache) {
    let score = 0;
    
    // 1. Priorité intrinsèque (40%)
    const prioriteScores = {
        'urgente': 100,
        'haute': 75,
        'normale': 50,
        'basse': 25
    };
    score += (prioriteScores[tache.priorite] || 0) * 0.4;
    
    // 2. Urgence temporelle (40%)
    if (tache.date_echeance) {
        const joursRestants = daysBetween(new Date(), tache.date_echeance);
        if (joursRestants < 0) {
            score += 100 * 0.4; // Retard = très urgent
        } else if (joursRestants <= 1) {
            score += 90 * 0.4;
        } else if (joursRestants <= 3) {
            score += 70 * 0.4;
        } else if (joursRestants <= 7) {
            score += 50 * 0.4;
        } else {
            score += 30 * 0.4;
        }
    }
    
    // 3. Dépendances (20%)
    if (tache.dependances && tache.dependances.length > 0) {
        score += 80 * 0.2; // Tâches bloquantes = importantes
    }
    
    return score;
}
```

#### Suggestions d'ordre

```javascript
function suggestTaskOrder(taches) {
    // 1. Trier par score de priorité
    const sorted = [...taches].sort((a, b) => {
        return calculateTaskPriority(b) - calculateTaskPriority(a);
    });
    
    // 2. Respecter les dépendances
    const ordered = [];
    const processed = new Set();
    
    function addWithDependencies(tache) {
        if (processed.has(tache.id)) return;
        
        // Ajouter d'abord les dépendances
        if (tache.dependances) {
            tache.dependances.forEach(depId => {
                const dep = taches.find(t => t.id === depId);
                if (dep) addWithDependencies(dep);
            });
        }
        
        ordered.push(tache);
        processed.add(tache.id);
    }
    
    sorted.forEach(addWithDependencies);
    return ordered;
}
```

---

## 📊 Modèle de données étendu

### Ajouts nécessaires à la table `taches`

```sql
ALTER TABLE taches ADD COLUMN dependances TEXT; -- JSON array des IDs
ALTER TABLE taches ADD COLUMN ordre_suggere INTEGER;
ALTER TABLE taches ADD COLUMN duree_minutes INTEGER;
ALTER TABLE taches ADD COLUMN date_debut DATE;
ALTER TABLE taches ADD COLUMN progression INTEGER DEFAULT 0; -- 0-100%
```

### Structure JSON des dépendances

```json
{
    "id": 5,
    "nom": "Tâche B",
    "dependances": [3, 4], // Attend la fin des tâches 3 et 4
    "duree_minutes": 120,
    "date_debut": "2025-10-06",
    "date_echeance": "2025-10-08",
    "progression": 0
}
```

---

## 🎨 Interface utilisateur

### Navigation entre vues

```html
<div class="planning-toolbar">
    <div class="view-selector">
        <button class="view-btn active" data-view="agenda">
            <i class="fas fa-calendar"></i> Agenda
        </button>
        <button class="view-btn" data-view="kanban">
            <i class="fas fa-columns"></i> Kanban
        </button>
        <button class="view-btn" data-view="timeline">
            <i class="fas fa-chart-gantt"></i> Timeline
        </button>
        <button class="view-btn" data-view="liste">
            <i class="fas fa-list"></i> Liste
        </button>
    </div>
    
    <button onclick="autoSchedule()" class="btn-auto-schedule">
        <i class="fas fa-wand-magic-sparkles"></i> Ordonnancer automatiquement
    </button>
</div>

<div id="planning-container">
    <!-- Vue active -->
</div>
```

---

## 🔄 Flux de données

### 1. Chargement initial

```
User ouvre Planning
    ↓
loadTaches() [api.js]
    ↓
calculatePriorities() [planning.js]
    ↓
renderActiveView() [planning.js]
    ↓
Affichage (Agenda/Kanban/Timeline)
```

### 2. Ordonnancement automatique

```
User click "Ordonnancer"
    ↓
suggestTaskOrder() [planning.js]
    ↓
Calcul des priorités + dépendances
    ↓
Proposition d'ordre
    ↓
User valide
    ↓
updateTaskOrder() [api.js]
    ↓
Mise à jour base de données
    ↓
Refresh vue
```

### 3. Drag & drop (Kanban)

```
User déplace tâche
    ↓
onTaskDrop(tacheId, newStatut)
    ↓
updateTache() [api.js]
    ↓
Mise à jour statut
    ↓
showNotification("Tâche déplacée")
```

---

## 📁 Structure des fichiers

```
public/js/
├── utils.js
├── components.js
├── api.js
├── ui.js
├── filters.js
├── modals.js
├── planning.js          ← NOUVEAU - Module principal
├── planning/
│   ├── agenda.js        ← Vue agenda (FullCalendar)
│   ├── kanban.js        ← Vue kanban (Sortable.js)
│   ├── timeline.js      ← Vue timeline (Vis.js)
│   ├── scheduling.js    ← Ordonnancement automatique
│   └── dependencies.js  ← Gestion des dépendances
└── app.js
```

---

## 🚀 Plan de développement

### Phase 1 : Fondations (2-3h)
- [x] Architecture et analyse
- [ ] Ajouter les colonnes DB nécessaires
- [ ] Créer `planning.js` avec structure de base
- [ ] Interface de navigation entre vues

### Phase 2 : Vue Kanban (2-3h)
- [ ] Implémenter le board Kanban
- [ ] Intégrer Sortable.js pour drag & drop
- [ ] Synchronisation avec l'API
- [ ] Compteurs et badges

### Phase 3 : Vue Agenda (3-4h)
- [ ] Intégrer FullCalendar
- [ ] Mapper les tâches vers les événements
- [ ] Drag & drop dans le calendrier
- [ ] Création rapide de tâches

### Phase 4 : Ordonnancement (3-4h)
- [ ] Algorithme de calcul de priorité
- [ ] Gestion des dépendances
- [ ] Suggestion d'ordre
- [ ] Interface de validation

### Phase 5 : Vue Timeline (4-5h) - Optionnel
- [ ] Intégrer Vis.js Timeline
- [ ] Diagramme de Gantt
- [ ] Visualisation des dépendances
- [ ] Édition des durées

### Phase 6 : Polish (2-3h)
- [ ] Tests complets
- [ ] Documentation
- [ ] Optimisations
- [ ] Mobile responsive

**Durée totale estimée : 16-22 heures**

---

## 💡 Fonctionnalités avancées (future)

### Court terme
- [ ] Notifications avant échéance
- [ ] Export iCal / Google Calendar
- [ ] Partage de planning
- [ ] Impression

### Moyen terme
- [ ] Assignation à plusieurs personnes
- [ ] Sous-tâches (checklist)
- [ ] Templates de tâches
- [ ] Récurrence (tâches répétitives)

### Long terme
- [ ] IA pour suggestions intelligentes
- [ ] Analyse de performance
- [ ] Gestion de ressources (capacité)
- [ ] Vue équipe (multi-utilisateurs)

---

## 🎓 Exemples de code

### Composant Kanban simplifié

```javascript
function renderKanbanView(taches) {
    const statuts = ['a_faire', 'en_cours', 'terminee', 'annulee'];
    
    return statuts.map(statut => {
        const tasks = taches.filter(t => t.statut === statut);
        
        return `
            <div class="kanban-column">
                <h3>${getStatutLabel(statut)} (${tasks.length})</h3>
                <div class="kanban-tasks sortable" data-status="${statut}">
                    ${tasks.map(t => KanbanCard(t)).join('')}
                </div>
            </div>
        `;
    }).join('');
}

function KanbanCard(tache) {
    return `
        <div class="kanban-card" data-id="${tache.id}">
            <div class="card-header">
                ${Badge(tache.priorite_libelle, tache.priorite_couleur)}
            </div>
            <div class="card-title">${tache.nom}</div>
            <div class="card-footer">
                ${tache.date_echeance ? formatDate(tache.date_echeance) : ''}
            </div>
        </div>
    `;
}
```

---

## 📊 Métriques de succès

- ✅ Temps moyen de planification réduit de 50%
- ✅ Taux d'utilisation des vues > 70%
- ✅ Satisfaction utilisateur > 8/10
- ✅ Tâches en retard réduites de 30%

---

## 🔐 Sécurité et performance

### Performance
- Pagination des tâches (max 100 à la fois)
- Lazy loading des vues
- Cache des calculs de priorité
- Debounce des mises à jour

### Sécurité
- Validation côté serveur
- Sanitization des données
- Droits d'accès (si multi-utilisateurs)

---

**Recommandation : Commencer par la Phase 1 (fondations) et Phase 2 (Kanban) pour une première version fonctionnelle rapidement.**

Voulez-vous que je commence l'implémentation ? 🚀

