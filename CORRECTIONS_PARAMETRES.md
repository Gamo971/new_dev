# Corrections - Système de Paramètres

## 🐛 Problèmes identifiés et corrigés

### Problème 1 : Erreur 500 sur `/api/parametres`
**Symptôme** :
```
Failed to load resource: the server responded with a status of 500 (Internal Server Error)
```

**Cause** :
- `ParametreController` utilisait `$this->jsonResponse()` 
- Mais `ApiController` n'avait que `sendJson()`
- Méthode manquante = erreur fatale PHP

**Solution** :
Ajout de la méthode `jsonResponse()` dans `ApiController` :
```php
protected function jsonResponse(array $data, int $statusCode = 200): void
{
    $this->sendJson($data, $statusCode);
}
```

**Fichier modifié** : `src/Controllers/ApiController.php`

---

### Problème 2 : Ordre des routes incorrect
**Symptôme** :
- Route `/api/parametres/batch` ne fonctionnait pas
- Interceptée par `/api/parametres/(\d+)`

**Cause** :
Routes génériques avec regex placées AVANT les routes spécifiques

**Solution** :
Réorganisation de l'ordre dans le Router :
```
AVANT (❌ incorrect) :
1. GET  /api/parametres
2. GET  /api/parametres/(\d+)           ← intercepte tout !
3. GET  /api/parametres/cle/([^/]+)    ← jamais atteinte
4. PUT  /api/parametres/batch          ← jamais atteinte

MAINTENANT (✅ correct) :
1. GET  /api/parametres
2. PUT  /api/parametres/batch          ← spécifique AVANT générique
3. GET  /api/parametres/cle/([^/]+)    ← spécifique AVANT générique
4. POST /api/parametres/([^/]+)/reset  ← spécifique AVANT générique
5. GET  /api/parametres/(\d+)          ← générique en dernier
6. POST /api/parametres
7. PUT  /api/parametres/(\d+)
8. DELETE /api/parametres/(\d+)
```

**Règle** : Les routes **spécifiques doivent toujours être déclarées AVANT les routes génériques** avec regex.

**Fichier modifié** : `src/Router/Router.php`

---

### Problème 3 : Cache navigateur
**Symptôme** :
- Changements dans les fichiers JS/CSS non pris en compte
- Ancienne version du code exécutée

**Solution** :
- Versioning des assets : `?v=2` → `?v=3`
- Forcer rechargement : **CTRL + SHIFT + R** (Windows/Linux) ou **CMD + SHIFT + R** (Mac)

**Fichiers modifiés** : `public/index.php`

---

## ✅ Résultats Attendus

Après les corrections :

1. ✅ L'onglet "Paramètres" se charge sans erreur
2. ✅ Les 7 jours de la semaine s'affichent
3. ✅ Les horaires par défaut sont visibles (09:00 - 18:00)
4. ✅ Le récapitulatif affiche : "5 jours travaillés, 35h/semaine"
5. ✅ Le bouton "Enregistrer" fonctionne
6. ✅ Les paramètres sont sauvegardés en base de données

---

## 🧪 Tests de Validation

### Test 1 : Chargement de l'onglet
```
1. Ouvrir http://localhost:8000
2. Cliquer sur "Paramètres" (icône ⚙️)
3. Vérifier : pas d'erreur console
4. Vérifier : les jours s'affichent (7 boutons)
```

### Test 2 : Modification et sauvegarde
```
1. Décocher "Samedi" et "Dimanche"
2. Modifier heures : 08:00 - 17:00
3. Cliquer "Enregistrer"
4. Vérifier : notification verte "14 paramètre(s) mis à jour"
5. Rafraîchir la page (F5)
6. Vérifier : changements conservés
```

### Test 3 : Intégration avec planification
```
1. Aller dans "Missions"
2. Créer une tâche avec échéance un lundi
3. Cliquer sur l'icône 🪄 (planification auto)
4. Vérifier : date planifiée = jour de semaine (pas weekend)
```

---

## 📁 Fichiers Modifiés

| Fichier | Changement | Lignes |
|---------|------------|--------|
| `src/Controllers/ApiController.php` | Ajout méthode `jsonResponse()` | +4 |
| `src/Router/Router.php` | Réorganisation ordre routes | Modif |
| `public/index.php` | Versioning v2 → v3 | ~15 |

---

## 🔍 Debugging

Si l'erreur 500 persiste, vérifier les logs PHP :

**Console navigateur (F12)** :
```javascript
// Vérifier la réponse de l'API
fetch('/api/parametres')
  .then(r => r.json())
  .then(console.log)
  .catch(console.error);
```

**Logs serveur** :
```
[Sun Oct  5 22:01:29 2025] [::1]:51321 [500]: GET /api/parametres
```

**Base de données** :
```bash
php src/Database/migrations/create_parametres_table.php
```

---

## ✨ Status Final

- ❌ **AVANT** : Erreur 500, onglet vide
- ✅ **MAINTENANT** : Onglet Paramètres fonctionnel avec 14 paramètres configurables

---

Date : 2025-10-06  
Corrections : 3  
Temps : ~10 minutes

