# Système de Planification avec Date de Planification

## 🎯 Objectif

Ajouter une distinction entre :
- **`date_echeance`** : La deadline réelle (date butoir du client)
- **`date_planifiee`** : La date où vous prévoyez de travailler sur cette tâche

## 📊 Fonctionnalités

### 1. Double système de dates
- Chaque tâche peut avoir une date d'échéance ET une date de planification
- La date de planification peut être différente (et antérieure) à la date d'échéance
- Exemple : 
  - Échéance : 15 janvier
  - Planification : 10 janvier (vous prévoyez de le faire 5 jours avant)

### 2. Planification automatique intelligente
- Calcul automatique de la date de planification suggérée
- Basé sur :
  - Priorité de la tâche
  - Date d'échéance
  - Temps estimé
  - Disponibilités dans l'agenda

### 3. Re-planification automatique
- Les tâches non effectuées à la date prévue sont automatiquement re-planifiées
- Algorithme de recherche du prochain créneau disponible
- Alertes pour les tâches dépassées

## 🏗️ Architecture technique

### Modifications de la base de données

```sql
ALTER TABLE taches ADD COLUMN date_planifiee DATE;
CREATE INDEX idx_taches_date_planifiee ON taches(date_planifiee);
```

### Modifications du modèle

**Classe `Tache`** :
- Propriété : `?\DateTime $datePlanifiee`
- Getter : `getDatePlanifiee(): ?\DateTime`
- Setter : `setDatePlanifiee(?\DateTime $date): void`
- Méthode : `isEnAvanceOuEnRetard(): string` (compare planifiée vs échéance)

### Algorithme de planification

```
FONCTION planifierTache(tache):
    1. Récupérer toutes les tâches déjà planifiées
    2. Calculer la marge idéale (échéance - temps_estimé - buffer)
    3. Chercher un créneau libre dans l'agenda
    4. Vérifier les contraintes :
       - Pas le week-end (optionnel)
       - Pas de conflit avec d'autres tâches
       - Respect de la capacité quotidienne (8h max par jour)
    5. Proposer ou assigner la date
```

### Algorithme de re-planification

```
FONCTION replanifierTachesPassees():
    1. Identifier les tâches avec date_planifiee < AUJOURD'HUI et statut != 'terminee'
    2. Pour chaque tâche :
       a. Calculer nouveau score de priorité (augmenté car en retard)
       b. Trouver le prochain créneau disponible
       c. Mettre à jour date_planifiee
    3. Notifier l'utilisateur des changements
```

## 📅 Intégration avec les vues

### Vue Agenda
- Afficher les tâches sur leur `date_planifiee` (pas date_echeance)
- Couleur différente si proche de l'échéance
- Badge "⚠️" si planifié trop tard par rapport à l'échéance

### Vue Kanban
- Badge supplémentaire montrant la date de planification
- Colonne "En retard de planification" pour les tâches non faites

### Vue Liste
- Tri par date de planification
- Colonne supplémentaire affichant date planifiée vs échéance

## 🎨 UI/UX

### Formulaire de tâche

```
┌─────────────────────────────────────────┐
│ Nom de la tâche : ____________          │
│                                         │
│ Date d'échéance : [15/01/2025]         │
│ Date planifiée  : [10/01/2025]         │
│                   ↑                     │
│     [📅 Planifier automatiquement]     │
│                                         │
│ ℹ️ Marge : 5 jours avant l'échéance   │
└─────────────────────────────────────────┘
```

### Boutons d'action
- "📅 Planifier automatiquement" : Calcule et assigne la date optimale
- "🔄 Re-planifier toutes" : Re-calcule toutes les tâches en retard
- "🎯 Suggérer planning de la semaine" : Vue hebdomadaire optimisée

## 📋 Cas d'usage

### Scénario 1 : Nouvelle tâche
1. Utilisateur crée une tâche avec échéance le 20/01
2. Clique sur "Planifier automatiquement"
3. Système propose le 15/01 (5 jours de marge)
4. Tâche apparaît le 15/01 dans l'agenda

### Scénario 2 : Tâche non effectuée
1. Tâche planifiée le 10/01 mais pas terminée
2. Le 11/01, système détecte le retard
3. Re-planification automatique au 12/01
4. Notification : "⚠️ 1 tâche re-planifiée"

### Scénario 3 : Optimisation hebdomadaire
1. Utilisateur clique "Suggérer planning semaine"
2. Système analyse toutes les tâches non planifiées
3. Répartit intelligemment sur la semaine
4. Affiche la proposition dans un modal
5. Utilisateur valide ou ajuste

## 🔧 Paramètres configurables

- **Marge par défaut** : 3 jours
- **Capacité journalière** : 8 heures
- **Jours travaillés** : Lundi-Vendredi
- **Re-planification automatique** : Oui/Non
- **Notification re-planification** : Oui/Non

## 📈 Bénéfices

✅ **Visibilité** : Savoir exactement quand travailler sur quoi  
✅ **Anticipation** : Éviter les urgences de dernière minute  
✅ **Équilibre** : Répartition équilibrée de la charge de travail  
✅ **Flexibilité** : Re-planification automatique en cas d'imprévu  
✅ **Productivité** : Focus sur ce qui doit être fait aujourd'hui  

## 🚀 Plan d'implémentation

### Phase 1 : Backend (30 min)
1. Migration base de données
2. Mise à jour modèle Tache
3. Mise à jour Repository
4. Tests unitaires

### Phase 2 : Frontend (45 min)
5. Ajout champ dans formulaire
6. Mise à jour de l'API JavaScript
7. Affichage dans les vues

### Phase 3 : Algorithmes (1h)
8. Algorithme de planification automatique
9. Algorithme de re-planification
10. Tests et ajustements

### Phase 4 : Intégration (30 min)
11. Boutons d'action
12. Modals de suggestion
13. Notifications

### Total estimé : ~2h30

## 📝 Notes techniques

- La `date_planifiee` peut être NULL (tâche non encore planifiée)
- Si NULL, la tâche n'apparaît pas dans l'agenda
- La `date_echeance` reste optionnelle
- Compatibilité ascendante : tâches existantes fonctionnent sans `date_planifiee`

## 🎯 Next Steps

Voulez-vous que je commence par :
1. **Option A** : Migration DB + Backend complet d'abord
2. **Option B** : Tout en une fois (backend + frontend + algo)
3. **Option C** : Prototype rapide (algo basique puis amélioration)

Quelle approche préférez-vous ?

