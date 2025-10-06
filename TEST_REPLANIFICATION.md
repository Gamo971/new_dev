# Test de la Re-planification Améliorée

## 📋 Fonctionnalité
Le bouton "Re-planifier les retards" gère maintenant **3 types de tâches** :

### ✅ CAS 1 : Tâches non planifiées
- **Condition** : `date_planifiee = null` ET `date_echeance != null`
- **Action** : Calcule automatiquement une `date_planifiee` optimale
- **Logique** : Utilise la priorité et le temps estimé

### ✅ CAS 2 : Planification dépassée
- **Condition** : `date_planifiee < aujourd'hui` ET `statut != 'terminee'`
- **Action** : Recalcule une nouvelle `date_planifiee`
- **Logique** : Re-planifie selon l'échéance restante

### ✅ CAS 3 : Échéance dépassée
- **Condition** : `date_echeance < aujourd'hui` ET `statut != 'terminee'`
- **Action** : Planifie dès aujourd'hui (urgence)
- **Logique** : Tâche critique à traiter immédiatement

---

## 🧪 Scénarios de Test

### Test 1 : Tâche non planifiée
**Données initiales** :
```json
{
  "nom": "Tâche sans planification",
  "date_echeance": "2025-10-15",
  "date_planifiee": null,
  "priorite": "haute",
  "statut": "en_cours"
}
```

**Résultat attendu** :
- `date_planifiee` calculée = 3 jours avant échéance
- `date_planifiee` = "2025-10-12"

---

### Test 2 : Planification dépassée
**Données initiales** :
```json
{
  "nom": "Tâche planifiée hier",
  "date_echeance": "2025-10-20",
  "date_planifiee": "2025-10-05",
  "priorite": "normale",
  "statut": "a_faire"
}
```

**Résultat attendu** :
- `date_planifiee` recalculée selon échéance
- Nouvelle `date_planifiee` = 5 jours avant échéance

---

### Test 3 : Échéance dépassée
**Données initiales** :
```json
{
  "nom": "Tâche en retard",
  "date_echeance": "2025-10-01",
  "date_planifiee": null,
  "priorite": "urgente",
  "statut": "en_cours"
}
```

**Résultat attendu** :
- `date_planifiee` = AUJOURD'HUI (2025-10-06)
- Tâche devient prioritaire dans l'agenda

---

### Test 4 : Tâche terminée (exclusion)
**Données initiales** :
```json
{
  "nom": "Tâche déjà finie",
  "date_echeance": "2025-09-30",
  "date_planifiee": "2025-09-20",
  "priorite": "haute",
  "statut": "terminee"
}
```

**Résultat attendu** :
- ❌ **IGNORÉE** - Ne doit PAS être re-planifiée
- Statut "terminee" ou "annulee" = exclusion automatique

---

### Test 5 : Plusieurs tâches mixtes
**Données initiales** :
```json
[
  { "date_planifiee": null, "date_echeance": "2025-10-15", "statut": "a_faire" },
  { "date_planifiee": "2025-10-05", "date_echeance": "2025-10-20", "statut": "en_cours" },
  { "date_planifiee": null, "date_echeance": "2025-10-01", "statut": "a_faire" },
  { "date_planifiee": "2025-09-30", "date_echeance": "2025-10-10", "statut": "terminee" }
]
```

**Résultat attendu** :
- Modal affiche : "📋 Tâches à re-planifier : 3"
  - • 2 non planifiée(s)
  - • 1 en retard de planification
  - • 0 avec échéance dépassée
- Tâche terminée ignorée

---

## 🎯 Message de Confirmation

Le modal affiche maintenant un résumé détaillé :

```
📋 Tâches à re-planifier : 7

• 3 non planifiée(s)
• 2 en retard de planification
• 2 avec échéance dépassée

Voulez-vous les re-planifier automatiquement ?
```

---

## 🎨 Message de Résultat

Après re-planification :

```
✅ 7 tâche(s) re-planifiée(s) avec succès !
```

En cas d'erreurs partielles :

```
✅ 5 tâche(s) re-planifiée(s) avec succès ! (2 erreur(s))
```

---

## 🔄 Actualisation Automatique

Après re-planification réussie :
1. ✅ `loadAllData()` - Recharge toutes les données
2. ✅ Si onglet Planning actif → rafraîchit la vue active (Kanban/Agenda/Liste)
3. ✅ Les badges et couleurs se mettent à jour automatiquement

---

## 📝 Checklist de Test Manuel

### Préparation
- [ ] Créer 2-3 tâches sans `date_planifiee` mais avec `date_echeance`
- [ ] Créer 1 tâche avec `date_planifiee` dans le passé
- [ ] Créer 1 tâche avec `date_echeance` dans le passé
- [ ] Créer 1 tâche terminée avec dates dans le passé

### Exécution
- [ ] Cliquer sur "Re-planifier les retards"
- [ ] Vérifier le message de confirmation (compteurs corrects)
- [ ] Valider la re-planification
- [ ] Attendre le message de succès

### Vérification
- [ ] Les tâches non planifiées ont maintenant une `date_planifiee`
- [ ] Les tâches en retard ont une nouvelle `date_planifiee`
- [ ] Les tâches terminées n'ont PAS été modifiées
- [ ] L'agenda affiche les nouvelles dates
- [ ] Les badges de statut sont corrects

---

## ✅ Code Modifié

### Fichier : `public/js/task-scheduler.js`
**Lignes** : 176-290
**Fonction** : `rescheduleLateTasks()`

### Fichier : `public/index.php`
**Ligne** : 406
**Tooltip** : Mis à jour pour refléter les 3 cas

---

## 🚀 Prêt pour le déploiement !

Date : 2025-10-06
Auteur : Garry
Status : ✅ **READY FOR TESTING**

