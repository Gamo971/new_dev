@echo off
set GIT_PAGER=
git config core.pager ""
git add .
git commit -m "feat: Module Planning complet avec vues Kanban/Agenda et ordonnancement automatique

- Ajout vue Kanban avec drag & drop
- Ajout vue Agenda/Calendrier
- Algorithme ordonnancement automatique
- Composants reutilisables
- Documentation complete"

git push origin feat/mission-manager
echo.
echo Push termine!
pause

