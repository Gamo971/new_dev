# Script pour commit et push vers GitHub
Write-Host "=== Préparation du commit ===" -ForegroundColor Cyan

# Configurer Git pour ne pas utiliser de pager
$env:GIT_PAGER = 'more'
$env:PAGER = 'more'

# Ajouter tous les fichiers
Write-Host "`nAjout des fichiers..." -ForegroundColor Yellow
git add .

# Afficher le statut
Write-Host "`nStatut Git:" -ForegroundColor Yellow
git status --short

# Commit
$commitMessage = @"
feat: Module Planning complet avec 3 vues et ordonnancement auto

- Ajout de la vue Kanban avec drag & drop (Sortable.js)
- Ajout de la vue Agenda/Calendrier (FullCalendar)
- Ajout de la vue Liste améliorée
- Algorithme d'ordonnancement automatique intelligent
- Création de composants réutilisables (Badge, Card, etc.)
- Extraction JavaScript en modules séparés (~580 lignes)
- CSS personnalisé pour le planning
- Documentation complète (4 fichiers MD, 1000+ lignes)

Fonctionnalités:
✅ Drag & drop Kanban entre colonnes
✅ Calendrier mensuel/hebdomadaire interactif
✅ Ordonnancement basé sur priorité, échéance, statut
✅ Interface responsive et moderne
✅ Synchronisation API temps réel

Bibliothèques:
- Sortable.js 1.15.0 (MIT)
- FullCalendar 6.1.9 (MIT)

Documentation:
- PLANNING_ARCHITECTURE.md
- PLANNING_PROPOSITION.md
- PLANNING_IMPLEMENTED.md
- public/js/planning/README.md
- REFACTORING.md
- COMPONENTS_REFACTORING.md
"@

Write-Host "`nCréation du commit..." -ForegroundColor Yellow
git commit -m $commitMessage

# Push vers origin
Write-Host "`nPush vers GitHub..." -ForegroundColor Yellow
$currentBranch = git branch --show-current
Write-Host "Branche actuelle: $currentBranch" -ForegroundColor Cyan
git push origin $currentBranch

Write-Host "`n=== Terminé! ===" -ForegroundColor Green
Write-Host "Branche $currentBranch poussée sur GitHub" -ForegroundColor Green

