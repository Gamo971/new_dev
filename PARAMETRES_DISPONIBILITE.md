# Système de Paramètres de Disponibilité

## 📋 Vue d'ensemble

Système complet permettant de définir les jours et horaires de travail, qui sont ensuite pris en compte automatiquement dans l'algorithme de planification des tâches.

---

## 🎯 Fonctionnalités

### 1. **Configuration des Jours de Travail** 📅
- Sélection visuelle des 7 jours de la semaine
- Interface interactive avec feedback visuel (vert = travaillé, gris = non travaillé)
- Par défaut : Lundi à Vendredi travaillés, weekend non travaillé

### 2. **Horaires de Travail** ⏰
- **Heure de début** (ex: 09:00)
- **Heure de fin** (ex: 18:00)
- **Durée de pause** en minutes (ex: 60 min)

### 3. **Capacité de Travail** 💪
- **Heures effectives par jour** : Temps réel de travail sur projets (ex: 7h)
- **Heures par semaine** : Total hebdomadaire (ex: 35h)
- Calcul automatique de cohérence

### 4. **Paramètres de Planification** ⚙️
- **Buffer de sécurité** : Marge ajoutée aux délais (ex: 20%)
- **Planification auto** : Activation/désactivation

### 5. **Récapitulatif Dynamique** 📊
- Affiche le nombre de jours travaillés
- Compare capacité calculée vs déclarée
- Alerte en cas d'incohérence

---

## 🗄️ Architecture Backend

### Table `parametres`
```sql
CREATE TABLE parametres (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    cle TEXT NOT NULL UNIQUE,
    valeur TEXT,
    type TEXT DEFAULT 'string',
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
)
```

### Paramètres par défaut créés
- `jours_travail_lundi` → `jours_travail_dimanche` (7 paramètres)
- `horaire_debut`, `horaire_fin`, `horaire_pause_duree`
- `heures_travail_par_jour`, `heures_travail_par_semaine`
- `buffer_planification`, `planification_auto_enabled`

**Total : 14 paramètres**

### Modèle `Parametre.php`
```php
class Parametre {
    private ?int $id;
    private string $cle;
    private ?string $valeur;
    private string $type; // 'string', 'boolean', 'number', 'integer', 'time'
    private ?string $description;
    private \DateTime $createdAt;
    private \DateTime $updatedAt;
    
    // Méthodes utilitaires statiques
    public static function isJourTravail(string $jour, array $parametres): bool
    public static function getHeuresTravailParJour(array $parametres): float
    public static function getBufferPlanification(array $parametres): float
}
```

### Repository `ParametreRepository.php`
**Méthodes principales** :
- `findAll()` : Tous les paramètres
- `findByCle(string $cle)` : Un paramètre par clé
- `getJoursTravail()` : Paramètres des jours
- `updateValeurByCle(string $cle, $valeur)` : Mise à jour rapide
- `updateMultiple(array $parametres)` : Mise à jour en masse
- `resetToDefault(string $cle)` : Réinitialisation

### Controller `ParametreController.php`
**API REST complète** :
```
GET    /api/parametres              → Liste tous (groupés par catégorie)
GET    /api/parametres/:id          → Récupère par ID
GET    /api/parametres/cle/:cle     → Récupère par clé
POST   /api/parametres              → Crée un paramètre
PUT    /api/parametres/:id          → Met à jour
PUT    /api/parametres/batch        → Mise à jour en masse
POST   /api/parametres/:cle/reset   → Réinitialise à défaut
DELETE /api/parametres/:id          → Supprime
```

---

## 🎨 Interface Frontend

### Nouvel Onglet "Paramètres"
- **Icône** : <i class="fas fa-cog"></i>
- **Position** : Après l'onglet "Planning"
- **Sections** :
  1. Jours de travail (grid 7 colonnes)
  2. Horaires de travail (3 champs)
  3. Capacité de travail (2 champs)
  4. Paramètres de planification (2 champs)
  5. Récapitulatif (calculs automatiques)

### Script `parametres.js`
**Fonctions principales** :
- `loadParametres()` : Charge depuis l'API
- `renderParametres()` : Affiche dans l'interface
- `renderJoursTravail()` : Affiche les 7 jours
- `updateJourStyle(checkbox)` : Feedback visuel
- `updateRecapitulatif()` : Calculs en temps réel
- `saveParametres()` : Sauvegarde en masse
- `getJoursTravail()` : Export des jours travaillés
- `isJourTravaille(date)` : Vérifie si un jour est ouvré

---

## 🧠 Algorithme de Planification Intelligent

### Nouvelles Fonctions Utilitaires
```javascript
isJourOuvre(date)              → Vérifie si date = jour travaillé
getProchainJourOuvre(date)     → Trouve le prochain jour ouvré
compterJoursOuvres(debut, fin) → Compte les jours ouvrés entre 2 dates
reculerJoursOuvres(date, n)    → Recule de N jours ouvrés
```

### `calculateSmartScheduling()` - Version Améliorée

**Avant** (simple) :
- Recule de N jours calendaires selon priorité
- Ne tient compte d'aucune disponibilité

**Maintenant** (intelligent) ✨ :
1. **Récupère les paramètres** : heures/jour, buffer de sécurité
2. **Calcule en jours ouvrés** selon priorité
3. **Applique le buffer** au temps estimé
4. **Calcule les jours nécessaires** : `(temps estimé × (1 + buffer)) / heures par jour`
5. **Prend le max** entre marge de priorité et jours nécessaires
6. **Recule de N jours OUVRÉS** depuis l'échéance
7. **Vérifie** que la date est un jour ouvré
8. **Ne planifie jamais** dans le passé ou un jour non travaillé

**Exemple** :
```
Tâche : Priorité "haute", 14h estimées, échéance : 20 oct
Paramètres : 7h/jour, 20% buffer, lun-ven travaillés

Calcul :
- Heures avec buffer : 14 × 1.2 = 16.8h
- Jours nécessaires : ⌈16.8 / 7⌉ = 3 jours ouvrés
- Marge priorité haute : 2 jours ouvrés
- Maximum : 3 jours ouvrés
- Date planifiée : 3 jours ouvrés avant le 20 oct = 15 oct (lundi)
  (Si 15 oct = dimanche → 16 oct)
```

### Impact sur `autoScheduleTask()`
- Simplifié : utilise directement `calculateSmartScheduling()`
- Message amélioré : "X jour(s) ouvré(s) avant échéance"

### Impact sur `rescheduleLateTasks()`
- Utilise automatiquement le nouvel algorithme
- Toutes les re-planifications respectent les jours ouvrés

---

## 📊 Fichiers Créés/Modifiés

| Fichier | Type | Lignes | Description |
|---------|------|--------|-------------|
| `src/Database/migrations/create_parametres_table.php` | Migration | 80 | Création table + 14 paramètres défaut |
| `src/Models/Parametre.php` | Modèle | 130 | Entité avec méthodes utilitaires |
| `src/Repositories/ParametreRepository.php` | Repository | 230 | CRUD complet + méthodes spécialisées |
| `src/Controllers/ParametreController.php` | Controller | 250 | API REST 8 endpoints |
| `src/Router/Router.php` | Config | +12 | Ajout 8 routes paramètres |
| `public/index.php` | Interface | +100 | Onglet Paramètres complet |
| `public/js/parametres.js` | Frontend | 240 | Gestion UI + API calls |
| `public/js/task-scheduler.js` | Logique | +130 | 4 fonctions utilitaires + algo amélioré |

**Total : ~1170 lignes de code**

---

## 🧪 Tests à Effectuer

### Test 1 : Configuration de Base
1. Aller dans l'onglet "Paramètres"
2. Décocher "Samedi" et "Dimanche"
3. Définir horaires : 09:00 - 18:00, pause 60 min
4. Heures effectives : 7h/jour, 35h/semaine
5. Cliquer "Enregistrer"
6. Vérifier le récapitulatif : "5 jours travaillés, 35h/semaine"

### Test 2 : Planification Simple
1. Créer une tâche avec échéance le vendredi
2. Priorité "normale"
3. Temps estimé : 7h
4. Cliquer sur le bouton "planification auto" 🪄
5. Vérifier que la date planifiée = 3 jours ouvrés avant (mardi)

### Test 3 : Gestion Weekend
1. Créer une tâche avec échéance le lundi
2. Priorité "haute" (2 jours ouvrés avant)
3. Planification auto
4. Vérifier que la date = jeudi précédent (pas samedi/dimanche)

### Test 4 : Tâche Longue Durée
1. Créer une tâche avec 21h estimées
2. Buffer à 20% → 25.2h avec buffer
3. 7h/jour → 4 jours ouvrés nécessaires
4. Vérifier que la planification prend 4 jours ouvrés

### Test 5 : Re-planification en Masse
1. Créer plusieurs tâches non planifiées
2. Définir des échéances variées
3. Cliquer "Re-planifier les retards"
4. Vérifier que toutes tombent sur des jours ouvrés

---

## 🎯 Avantages du Système

### ✅ **Réalisme**
- Planification basée sur votre emploi du temps réel
- Aucune tâche ne tombe un jour non travaillé
- Calcul précis du temps disponible

### ✅ **Flexibilité**
- Configuration personnalisable par utilisateur
- Buffer ajustable selon votre marge de confort
- Activation/désactivation de la planification auto

### ✅ **Automatisation**
- Recalcul automatique lors des modifications
- Re-planification intelligente en un clic
- Alertes en cas d'incohérence

### ✅ **Visibilité**
- Récapitulatif en temps réel
- Compteur de jours ouvrés dans les notifications
- Feedback visuel sur les jours travaillés

---

## 🚀 Évolutions Futures Possibles

### Court terme
- [ ] Import/export de profils de disponibilité
- [ ] Gestion des jours fériés
- [ ] Horaires différenciés par jour

### Moyen terme
- [ ] Gestion des congés/absences
- [ ] Calendrier visuel des disponibilités
- [ ] Statistiques de charge de travail

### Long terme
- [ ] Multi-utilisateurs avec disponibilités différentes
- [ ] Synchronisation avec Google Calendar
- [ ] Alertes de surcharge (trop de tâches/jour)
- [ ] Optimisation automatique de la répartition

---

## 📝 Notes Techniques

### Compatibilité
- Fonctionne même si paramètres non chargés (fallback par défaut)
- Gestion d'erreur robuste (max 14 jours de recherche)
- Type safety avec validation côté backend

### Performance
- Mise à jour en masse (1 requête pour tous les paramètres)
- Calculs côté client (pas de surcharge serveur)
- Cache des paramètres en mémoire (variable globale `parametresData`)

### Sécurité
- Validation des types côté backend
- Contrainte UNIQUE sur les clés
- Pas d'injection SQL (requêtes préparées)

---

## ✅ Status

**Date** : 2025-10-06  
**Auteur** : Garry + AI Assistant  
**Version** : 1.0  
**Status** : ✅ **READY FOR TESTING**  

**Prochaine étape** : Tests utilisateurs et ajustements selon feedback.

