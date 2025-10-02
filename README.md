# PHP Cursor Starter

Starter minimal pour rendre Cursor autonome (édition, exécution, tests).

## Prérequis
- PHP 8.2+ et Composer
- (Optionnel) Node 18+ si vous voulez `npm run format` (Prettier)

## Démarrage rapide
```bash
composer install
composer start
```

Tests / qualité :
```bash
composer test
composer lint
composer typecheck
composer format
```

Variables d'environnement :
- Copiez `.env.example` vers `.env`
- Évitez d'y mettre des secrets en clair côté repo.

## Commandes canoniques (pour Cursor)
- `composer start` : lance un serveur local sur http://localhost:8000
- `composer test` : lance les tests Pest
- `composer lint` : vérifications PSR-12
- `composer typecheck` : analyse statique PHPStan
- `composer format` : auto-fix du style

## Déploiement (exemple)
- Utilisez le plugin FTPS de Cursor **vers un dossier de staging** séparé de la production.
- Base de données de staging distincte.

## Structure
- `public/` : fichiers exposés (index.php)
- `src/` : code applicatif
- `tests/` : tests Pest
- `ai/AI_RULES.md` : règles pour l'agent Cursor