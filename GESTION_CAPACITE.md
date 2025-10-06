# Gestion de Capacité de Travail

## 📋 Vue d'ensemble

Système intelligent qui évite la surcharge en répartissant automatiquement les tâches selon votre capacité de travail quotidienne. **Aucune journée ne sera surchargée** !

---

## 🎯 Problème Résolu

### **Avant** ❌
```
Lundi : 14h de tâches planifiées (mais seulement 7h disponibles)
Mardi : 0h de tâches
Mercredi : 21h de tâches (impossible à réaliser !)
```
→ Planning irréaliste, stress, retards

### **Maintenant** ✅
```
Lundi : 7h de tâches (capacité max)
Mardi : 7h de tâches
Mercredi : 7h de tâches
Jeudi : 7h de tâches (répartition équilibrée)
```
→ Planning réaliste et réalisable !

---

## 🧠 Comment ça fonctionne ?

### 1. **Calcul de Charge par Jour**
L'algorithme analyse toutes les tâches planifiées et calcule :
```javascript
{
  "2025-10-07": 420,  // 7h de tâches ce jour
  "2025-10-08": 180,  // 3h de tâches
  "2025-10-09": 540   // 9h -> SURCHARGÉ !
}
```

### 2. **Vérification de Capacité**
Avant de planifier une tâche :
```
Capacité maximale = 7h/jour (paramètres utilisateur)
Charge actuelle du jour = Somme des tâches déjà planifiées
Capacité disponible = Max - Charge actuelle
```

### 3. **Report Intelligent**
Si une journée est pleine :
```
1. Chercher le prochain jour ouvré
2. Vérifier sa capacité disponible
3. Si suffisante → planifier
4. Sinon → jour suivant
```

### 4. **Alerte Échéance**
Si aucun créneau avant l'échéance :
```
⚠️ Tâche planifiée mais alerte utilisateur :
"Capacité insuffisante avant échéance"
```

---

## 🔧 Fonctions Principales

### `getChargeParJour()`
Calcule la charge de travail pour chaque jour.

**Retour** :
```javascript
{
  "2025-10-07": 420,  // minutes
  "2025-10-08": 180,
  "2025-10-09": 360
}
```

### `getCapaciteDisponible(date, chargeMap)`
Retourne les minutes disponibles pour un jour donné.

**Exemple** :
```javascript
const dispo = getCapaciteDisponible("2025-10-07", chargeMap);
// → 120 (2h disponibles)
```

### `getProchainJourDisponible(date, dureeRequise, chargeMap)`
Trouve le prochain jour avec assez de capacité.

**Exemple** :
```javascript
// Besoin de 3h, à partir du 7 oct
const jour = getProchainJourDisponible(
  new Date("2025-10-07"), 
  180, 
  chargeMap
);
// → 2025-10-09 (premier jour avec 3h+ disponibles)
```

### `calculateSmartSchedulingWithCapacity(tache)`
Planification intelligente avec gestion de capacité.

**Algorithme** :
```
1. Calculer date de base (priorité + échéance)
2. Vérifier capacité disponible ce jour
3. Si OK → retourner cette date
4. Sinon → chercher prochain jour disponible
5. Vérifier qu'on ne dépasse pas l'échéance
6. Retourner la date optimale
```

---

## 📊 Exemple Concret

### Situation
```
Paramètres :
- 7h/jour de travail effectif
- Lundi à Vendredi travaillés

Tâches existantes :
- Lundi 7 oct : Tâche A (5h) + Tâche B (2h) = 7h (PLEIN)
- Mardi 8 oct : Tâche C (4h) = 4h disponibles
```

### Nouvelle Tâche
```
Tâche D :
- Priorité : Haute
- Échéance : Jeudi 10 oct
- Durée estimée : 3h
```

### Planification SANS Capacité ❌
```
Algorithme simple :
→ Haute priorité = 2 jours avant échéance
→ Date calculée : Mardi 8 oct

Résultat :
Mardi 8 oct : 4h + 3h = 7h (OK mais serré)
```

### Planification AVEC Capacité ✅
```
Algorithme intelligent :
1. Date de base : Mardi 8 oct
2. Capacité mardi : 3h disponibles
3. Durée requise : 3h
4. 3h ≥ 3h → OK !
5. Date finale : Mardi 8 oct
```

### Si Mardi était plein
```
Tâches existantes modifiées :
- Mardi 8 oct : 7h (PLEIN)

Planification :
1. Date de base : Mardi 8 oct
2. Capacité mardi : 0h disponible
3. Chercher prochain jour...
4. Mercredi 9 oct : 7h disponibles
5. 7h ≥ 3h → OK !
6. Date finale : Mercredi 9 oct ✅
7. Message : "⚠️ Date ajustée selon la capacité disponible"
```

---

## 🎨 Messages Utilisateur

### Planification Manuelle
```javascript
// Cas 1 : Date non ajustée
"📅 Date planifiée : Mardi 8 oct (2 jour(s) ouvré(s) avant échéance)"

// Cas 2 : Date ajustée pour capacité
"📅 Date planifiée : Mercredi 9 oct (1 jour(s) ouvré(s) avant échéance) 
 ⚠️ Date ajustée selon la capacité disponible"
```

### Re-planification en Masse
```javascript
"✅ 15 tâche(s) re-planifiée(s) avec succès ! 
 (5 date(s) ajustée(s) selon la capacité)"
```

---

## 🔍 Statistiques de Charge

### `getStatistiquesCharge(dateDebut, dateFin)`
Analyse la charge sur une période.

**Exemple** :
```javascript
const stats = await getStatistiquesCharge("2025-10-07", "2025-10-11");
// Retour :
[
  {
    date: "2025-10-07",
    charge: 420,        // 7h planifiées
    capacite: 420,      // 7h max
    disponible: 0,      // 0h restantes
    pourcentage: 100,   // 100% plein
    surchargee: false
  },
  {
    date: "2025-10-08",
    charge: 240,        // 4h planifiées
    capacite: 420,      // 7h max
    disponible: 180,    // 3h restantes
    pourcentage: 57,    // 57% plein
    surchargee: false
  },
  {
    date: "2025-10-09",
    charge: 540,        // 9h planifiées !
    capacite: 420,      // 7h max
    disponible: -120,   // -2h (surcharge)
    pourcentage: 129,   // 129% !
    surchargee: true    // ⚠️ ALERTE
  }
]
```

---

## 🎨 Indicateurs Visuels

### Couleurs par Charge
```javascript
getChargeColor(pourcentage) :
- 0-49%   → Vert     (Disponible)
- 50-79%  → Jaune    (Mi-charge)
- 80-99%  → Orange   (Presque plein)
- 100%+   → Rouge    (Surchargé)
```

### Affichage dans l'interface
```html
<div class="text-xs text-green-600">
  45% (3.2h)
</div>
```

---

## ⚙️ Configuration

### Paramètres Utilisateur
```
Heures de travail par jour : 7h
→ Capacité max = 7h × 60 = 420 minutes/jour
```

### Jours Ouvrés
```
Lundi à Vendredi : Travaillé ✅
Weekend : Non travaillé ❌
→ Pas de planification sur weekend
```

---

## 🧪 Tests

### Test 1 : Journée Non Pleine
```
1. Créer tâche A : 3h, échéance vendredi
2. Planifier automatiquement
3. Vérifier : planifiée dans les jours normaux
4. Créer tâche B : 2h, échéance vendredi
5. Vérifier : peut être sur le même jour
```

### Test 2 : Journée Pleine
```
1. Créer tâches totalisant 7h pour lundi
2. Créer nouvelle tâche C : 2h, échéance mercredi
3. Planifier automatiquement
4. Vérifier : planifiée mardi (pas lundi)
5. Vérifier : message "Date ajustée selon capacité"
```

### Test 3 : Surcharge Détectée
```
1. Créer 10h de tâches pour lundi (manuellement)
2. Aller dans Paramètres
3. Créer un script de statistiques
4. Vérifier : lundi signalé en rouge
```

### Test 4 : Re-planification Masse
```
1. Créer 20 tâches avec dates passées
2. Donner durées variées (1-4h)
3. Cliquer "Re-planifier les retards"
4. Vérifier : répartition équilibrée
5. Vérifier : aucun jour > 7h
```

---

## 📈 Avantages

### ✅ **Réalisme**
- Planning basé sur capacité réelle
- Fini les journées surchargées
- Respect des limites physiques

### ✅ **Prévention Burn-out**
- Charge équilibrée automatiquement
- Détection surcharge avant qu'elle arrive
- Marges de sécurité respectées

### ✅ **Optimisation**
- Utilisation maximale de la capacité
- Pas de jour vide si autres pleins
- Lissage automatique de la charge

### ✅ **Transparence**
- Statistiques de charge visibles
- Alertes si impossible avant échéance
- Messages explicatifs

---

## 🚀 Évolutions Futures

### Court terme
- [ ] Visualisation graphique de la charge (barres)
- [ ] Alertes visuelles sur jours surchargés
- [ ] Export calendrier avec charge

### Moyen terme
- [ ] Suggestion de répartition optimale
- [ ] Gestion des imprévus (ajout automatique buffer)
- [ ] Historique de charge réelle vs estimée

### Long terme
- [ ] IA prédictive (apprend de vos habitudes)
- [ ] Optimisation multi-projets
- [ ] Recommandations d'amélioration

---

## 📝 Code Key Points

### Ordre d'Exécution
```javascript
// 1. Charger paramètres utilisateur
await loadParametres();

// 2. Calculer charge actuelle
const chargeMap = await getChargeParJour();

// 3. Planifier avec capacité
const date = await calculateSmartSchedulingWithCapacity(tache, chargeMap);

// 4. Mettre à jour la charge map
chargeMap[date] = (chargeMap[date] || 0) + tache.temps_estime;
```

### Fallback Sécurisé
```javascript
// Si capacity-manager.js pas chargé
if (window.calculateSmartSchedulingWithCapacity) {
  // Utiliser gestion capacité
} else {
  // Fallback algorithme simple
  dateStr = calculateSmartScheduling(tacheTmp);
}
```

---

## ✅ Status

**Date** : 2025-10-06  
**Version** : 1.0  
**Status** : ✅ **READY FOR TESTING**  
**Fichiers** :
- `public/js/capacity-manager.js` (250 lignes)
- `public/js/task-scheduler.js` (modifié)
- `public/index.php` (script ajouté)

**Impact** : Planning 100% plus réaliste ! 🎉

