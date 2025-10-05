@echo off
set GIT_PAGER=
git config core.pager ""
git add -A
git commit -m "chore: Nettoyage des fichiers temporaires et documentation"
git push origin feat/mission-manager
echo.
echo Nettoyage termine!

