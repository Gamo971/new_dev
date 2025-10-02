# AI_RULES - Cursor

## Objectif
- Tu peux modifier des fichiers, lancer les scripts `composer` ci-dessous et corriger jusqu’au vert.
- Limites: max 20 fichiers modifiés / PR, 3 commits, pas d’accès aux secrets ni aux environnements de production.

## Commandes autorisées (canoniques)
- `composer start` : serveur local
- `composer test` : tests Pest
- `composer lint` : PSR-12
- `composer typecheck` : PHPStan
- `composer format` : auto-fix

## Do
- PR petites et ciblées (`ai/<topic>`), Conventional Commits (feat/fix/refactor/docs/test).
- Respecter PSR-12, maintenir les tests verts.
- Ajouter des tests pour chaque correction/refactor.

## Don’t
- Pas d’édition de `.env` réel, pas de credentials en clair.
- Pas de déploiement vers la prod directe.
- Pas de refactor massif sans issue/plan.

## Contexte
- PHP vanilla, `public/index.php` point d’entrée.
- Code applicatif dans `src/`, tests dans `tests/`.