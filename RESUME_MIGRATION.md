# 📌 Résumé pour ChatGPT - Migration vers serveur en ligne

## 🎯 Contexte du projet

Application web de gestion de missions pour un cabinet de conseil.
- **Stack:** PHP 8.2+ vanilla, SQLite, JavaScript ES6, TailwindCSS
- **Architecture:** MVC avec API REST (48 routes)
- **Base de données:** SQLite (fichier unique `storage/database.sqlite`)
- **Serveur dev actuel:** `php -S localhost:8000 -t public`

## 📊 Chiffres clés

- 3500 lignes PHP, 2500 lignes JavaScript
- 5 tables (clients, contacts, missions, tâches, paramètres)
- 48 routes API REST
- Aucune authentification actuellement

## 🏗️ Structure importante

```
/
├── public/           ← SEUL dossier public (document root)
│   ├── index.php    ← Point d'entrée unique
│   ├── css/
│   └── js/
├── src/             ← Code PHP (DOIT être hors document root)
├── storage/         ← Base SQLite (DOIT être hors document root)
└── vendor/          ← Dépendances Composer
```

## ⚠️ Prérequis serveur CRITIQUES

1. **PHP 8.2+** (projet utilise fonctionnalités modernes)
2. **Extension PDO SQLite** activée
3. **Mod_rewrite** (Apache) ou équivalent Nginx
4. **Permissions écriture** sur dossier `storage/`
5. **Composer** sur le serveur

## 🔒 Sécurité IMPORTANTE

❌ **Actuellement manquant:**
- Authentification utilisateur
- Protection routes API
- HTTPS obligatoire
- Rate limiting

✅ **Déjà présent:**
- Requêtes préparées PDO (injection SQL)
- Validation des entrées
- Sanitization des données

## 🚀 Configuration Apache nécessaire

```apache
# Dans public/.htaccess
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^api/.* index.php [L,QSA]
Options -Indexes
```

## 🚀 Configuration Nginx alternative

```nginx
location /api/ {
    try_files $uri $uri/ /index.php?$query_string;
}
```

## 📋 Checklist de migration

1. ✅ Vérifier PHP 8.2+ sur serveur
2. ✅ Structure: `public/` = document root, reste hors document root
3. ✅ Transférer fichiers (Git recommandé)
4. ✅ `composer install --no-dev --optimize-autoloader`
5. ✅ Créer `.env` avec `DATABASE_PATH=../storage/database.sqlite`
6. ✅ Permissions: `chmod 755 storage/ && chmod 664 storage/database.sqlite`
7. ✅ Configurer Apache/Nginx pour réécriture d'URL
8. ✅ Tester routes API
9. ⚠️ **AJOUTER authentification avant production**
10. ✅ Activer HTTPS
11. ✅ Configurer backups automatiques SQLite

## ⚡ Point d'attention: TailwindCSS

**Actuellement:** CDN (développement seulement)
**Production:** Compiler localement

```bash
npm install -D tailwindcss
npx tailwindcss -i ./public/css/input.css -o ./public/css/style.css --minify
```

## 🗄️ SQLite vs MySQL

**Rester sur SQLite si:**
- < 100 000 lignes
- < 50 utilisateurs simultanés
- Charge modérée

**Migrer vers MySQL si:**
- Forte charge
- Besoins de réplication
- > 100 000 lignes

Migration simple car PDO abstraction déjà en place.

## 🔥 Points CRITIQUES pour production

1. **AUTHENTIFICATION MANQUANTE** - à implémenter absolument
2. **HTTPS OBLIGATOIRE** - certificat SSL
3. **Backups SQLite** - automatiser (cron)
4. **Logs erreurs** - mettre en place monitoring
5. **Permissions fichiers** - storage/ en écriture uniquement pour www-data

## 📊 Routes API principales

- `/api/missions` - CRUD missions
- `/api/taches` - CRUD tâches
- `/api/taches/mission/{id}` - Tâches d'une mission
- `/api/clients` - CRUD clients
- `/api/contacts` - CRUD contacts
- `/api/parametres` - Configuration

Toutes retournent JSON: `{"success": true, "data": {...}}`

## 🎯 Questions clés pour ChatGPT

1. **Quel type d'hébergement recommandez-vous?**
   - VPS (DigitalOcean, Linode)?
   - Hébergement mutualisé PHP?
   - Serveur dédié?

2. **Configuration serveur optimale?**
   - Apache vs Nginx?
   - PHP-FPM?
   - OPcache settings?

3. **Sécurité: quel système d'authentification?**
   - JWT?
   - Sessions PHP?
   - OAuth2?

4. **SQLite suffit-il ou migrer vers MySQL?**

5. **Backups: quelle stratégie?**
   - Cron quotidien?
   - Service tiers?
   - Git LFS?

6. **Monitoring: quels outils recommandés?**

## 📁 Fichier complet disponible

`COMPTE_RENDU_PROJET.md` - 748 lignes avec tous les détails

---

**À copier/coller à ChatGPT pour démarrer la discussion sur la migration.**

