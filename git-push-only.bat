@echo off
set GIT_PAGER=
git config core.pager ""
echo Pushing vers GitHub...
git push origin feat/mission-manager
echo.
echo Push termine!

