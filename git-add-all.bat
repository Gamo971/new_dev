@echo off
set GIT_PAGER=
git config core.pager ""
echo Ajout de tous les fichiers...
git add .
echo.
echo Commit...
git commit -m "feat: Module Planning complet avec vues Kanban/Agenda et ordonnancement automatique"
echo.
echo Push vers GitHub...
git push origin feat/mission-manager
echo.
echo Termine!

