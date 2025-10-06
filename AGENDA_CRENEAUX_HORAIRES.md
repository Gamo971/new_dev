# Agenda avec Créneaux Horaires Intelligents

## 📋 Vue d'ensemble

Les tâches sont maintenant affichées dans des **créneaux horaires précis** dans l'agenda (vue semaine), au lieu d'être dans la section "all-day". Le système calcule automatiquement les heures de début et de fin selon vos horaires de travail.

---

## 🎯 Avant / Après

### **Avant** ❌
```
all-day  │ Tâche A │ Tâche B │ Tâche C │
─────────┼─────────┼─────────┼─────────┤
09:00    │         │         │         │
10:00    │         │         │         │
11:00    │         │         │         │
...
```
→ Pas de visibilité sur QUAND faire les tâches

### **Maintenant** ✅
```
all-day  │   🏁 Échéances en arrière-plan  │
─────────┼─────────────────────────────────┤
09:00    │ ┌─ Tâche A (2h) ──────────────┐ │
10:00    │ │                              │ │
11:00    │ └──────────────────────────────┘ │
11:00    │ ┌─ Tâche B (1h30) ────────────┐ │
12:00    │ │                              │ │
12:30    │ └──────────────────────────────┘ │
12:30    │ ═════ PAUSE DÉJEUNER (1h) ═════ │
13:30    │ ┌─ Tâche C (3h) ──────────────┐ │
14:30    │ │                              │ │
15:30    │ │                              │ │
16:30    │ └──────────────────────────────┘ │
```
→ Planning visuel réaliste et actionnable !

---

## 🧠 Algorithme de Placement

### **1. Groupement par Date**
Toutes les tâches planifiées le même jour sont regroupées.

### **2. Tri par Ordre de Création**
Les tâches sont triées par ID croissant (ordre de création).

### **3. Placement Séquentiel**
```javascript
Pour chaque tâche {
  1. Commencer à l'heure actuelle (ou horaire_debut)
  2. Vérifier si on est dans la pause → sauter après
  3. Calculer heure de fin = heure actuelle + durée
  4. Si chevauche la pause → prolonger après la pause
  5. Si dépasse horaire_fin → reporter au lendemain
  6. Sinon → placer la tâche
  7. Mettre à jour heure actuelle
}
```

### **4. Gestion de la Pause**
```
Pause calculée = Milieu de la journée ± durée pause / 2

Exemple :
- Horaires : 09:00 - 18:00 (9h)
- Milieu : 13:30
- Pause 60 min : 13:00 - 14:00
```

### **5. Débordement**
Si une tâche ne peut pas tenir dans la journée :
- Elle est reportée au jour suivant
- Commence à horaire_debut du lendemain
- Marquée avec ⚠️ (orange)
- Propriété `overflow: true`

---

## 🔧 Fonctions Principales

### `calculateTimeSlots(tachesDuJour, date)`
Calcule les créneaux horaires pour toutes les tâches d'un jour.

**Paramètres** :
- `tachesDuJour` : Array des tâches à placer
- `date` : Date au format YYYY-MM-DD

**Retourne** :
```javascript
[
  {
    ...tache,
    scheduledDate: "2025-10-07",
    scheduledStart: "09:00",
    scheduledEnd: "11:00",
    overflow: false
  },
  {
    ...tache,
    scheduledDate: "2025-10-08", // Reportée !
    scheduledStart: "09:00",
    scheduledEnd: "10:30",
    overflow: true
  }
]
```

### `timeToMinutes(time)` & `minutesToTime(minutes)`
Convertisseurs pour faciliter les calculs.

**Exemples** :
```javascript
timeToMinutes("09:30")  // → 570
minutesToTime(570)      // → "09:30"
```

### `getNextDate(dateStr)`
Retourne la date du lendemain.

```javascript
getNextDate("2025-10-07")  // → "2025-10-08"
```

---

## 📊 Exemple Concret

### **Données** :
```
Paramètres :
- Horaires : 09:00 - 18:00
- Pause : 60 min

Tâches planifiées le 2025-10-07 :
1. Tâche A (ID=1) : 2h (120 min)
2. Tâche B (ID=2) : 1h30 (90 min)
3. Tâche C (ID=3) : 3h (180 min)
4. Tâche D (ID=4) : 2h (120 min)
```

### **Calcul** :

```
09:00  ┌────────────────┐  Tâche A (2h)
       │                │
11:00  └────────────────┘
11:00  ┌───────────┐       Tâche B (1h30)
       │           │
12:30  └───────────┘
12:30  ═════════════════  PAUSE (1h) 
13:30  
13:30  ┌────────────────┐  Tâche C (3h)
       │                │
       │                │
16:30  └────────────────┘
16:30  ┌────────────────┐  Tâche D (2h)
       │                │  ⚠️ Dépasse 18:00 !
18:30  └────────────────┘  → Reportée au 08/10 à 09:00
```

### **Résultat FullCalendar** :
```javascript
[
  {
    title: "📅 Tâche A",
    start: "2025-10-07T09:00:00",
    end: "2025-10-07T11:00:00"
  },
  {
    title: "📅 Tâche B",
    start: "2025-10-07T11:00:00",
    end: "2025-10-07T12:30:00"
  },
  {
    title: "📅 Tâche C",
    start: "2025-10-07T13:30:00",
    end: "2025-10-07T16:30:00"
  },
  {
    title: "⚠️ Tâche D", // Orange
    start: "2025-10-08T09:00:00",
    end: "2025-10-08T11:00:00"
  }
]
```

---

## 🎨 Indicateurs Visuels

### **Couleurs** :
- 📅 **Vert/Bleu/Orange/Rouge** : Selon priorité et statut
- ⚠️ **Orange** : Tâche reportée (débordement)
- 🏁 **Gris** : Échéances (arrière-plan)

### **Édition Interactive** :
1. **Drag & Drop** : Déplacer une tâche change sa `date_planifiee`
2. **Redimensionnement** : Étirer une tâche change son `temps_estime`
3. **Clic** : Ouvre le modal de modification

---

## ⚙️ Configuration FullCalendar

### **Vue par Défaut** :
```javascript
initialView: 'timeGridWeek'  // Vue semaine avec heures
```

### **Plages Horaires** :
```javascript
slotMinTime: horaireDebut,   // Ex: "09:00"
slotMaxTime: horaireFin,     // Ex: "18:00"
slotDuration: '00:30:00',    // Créneaux de 30 min
```

### **Heures Ouvrables** :
```javascript
businessHours: {
  daysOfWeek: [1, 2, 3, 4, 5],  // Lun-Ven
  startTime: "09:00",
  endTime: "18:00"
}
```

### **Indicateur Temps Réel** :
```javascript
nowIndicator: true  // Ligne rouge = heure actuelle
```

---

## 🆕 Nouvelles Fonctionnalités

### 1️⃣ **Redimensionnement de Tâche**
```
Action : Étirer/réduire une tâche dans l'agenda
Résultat : Met à jour `temps_estime` automatiquement
Notification : "Durée mise à jour : 2h30"
```

### 2️⃣ **Détection Débordement**
```
Si tâche dépasse l'horaire de fin :
→ Reportée au lendemain
→ Affichée avec ⚠️
→ Couleur orange
```

### 3️⃣ **Gestion Pause Automatique**
```
Tâche qui chevauche la pause :
→ Prolongée automatiquement après la pause
→ Pas de coupure, mais décalage
```

---

## 🧪 Tests

### **Test 1 : Placement Simple**
```
Créer 3 tâches de 1h chacune pour demain
→ Vérifier : 09:00-10:00, 10:00-11:00, 11:00-12:00
```

### **Test 2 : Gestion Pause**
```
Créer une tâche de 3h à 11:00
Pause : 12:30-13:30
→ Vérifier : Tâche affichée 11:00-12:30, puis 13:30-15:00
```

### **Test 3 : Débordement**
```
Créer 10h de tâches pour un jour (capacité 7h)
→ Vérifier : 3h reportées au lendemain avec ⚠️
```

### **Test 4 : Redimensionnement**
```
Étirer une tâche de 1h à 2h dans l'agenda
→ Vérifier : Notification + temps_estime mis à jour
```

---

## 📝 Durées par Défaut

### **Si `temps_estime` non défini** :
```javascript
const dureeMinutes = parseInt(tache.temps_estime) || 30;
```
→ Durée par défaut : **30 minutes**

### **Affichage** :
```
Tâche sans durée → Bloc de 30 min
Tâche 15 min → Petit bloc
Tâche 4h → Grand bloc
```

---

## 🔄 Intégration avec Capacité

Le système de créneaux horaires fonctionne **en complément** de la gestion de capacité :

1. **Gestion de capacité** : S'assure qu'on ne planifie pas trop de tâches par jour
2. **Créneaux horaires** : Organise visuellement les tâches dans la journée

**Exemple** :
```
Capacité : Dit "7h max par jour"
Créneaux : Dit "De 09:00 à 18:00 avec pause à 12:30"
```

---

## 📁 Fichiers Modifiés

| Fichier | Changement | Lignes |
|---------|------------|--------|
| `public/js/planning/agenda.js` | Ajout système de créneaux | +150 |
| `public/index.php` | Versioning v3 → v4 | Modif |

---

## 🚀 Évolutions Futures

### Court terme
- [ ] Affichage visuel de la pause (barre grise)
- [ ] Drag & drop intelligent (snap aux créneaux)
- [ ] Couleur différente si tâche en retard

### Moyen terme
- [ ] Vue "Timeline" multi-jours
- [ ] Optimisation automatique de l'ordre
- [ ] Suggestion de meilleur créneau

### Long terme
- [ ] Prédiction durée réelle vs estimée
- [ ] Synchronisation Google Calendar
- [ ] Gestion des interruptions/imprévus

---

## ✅ Status

**Date** : 2025-10-06  
**Version** : 1.0  
**Status** : ✅ **READY FOR TESTING**  

**Impact** :
- Planning visuel réaliste ✅
- Respect horaires de travail ✅
- Gestion pause automatique ✅
- Débordement intelligent ✅
- Édition interactive ✅

---

## 🎉 Résultat Final

Un agenda qui ressemble à un **vrai planning** :
- Tâches placées dans des créneaux horaires précis
- Respect de vos horaires de travail
- Gestion automatique de la pause déjeuner
- Report intelligent si journée pleine
- Édition drag & drop fluide

**Testez en vue "Semaine" et admirez le résultat ! 🚀**

