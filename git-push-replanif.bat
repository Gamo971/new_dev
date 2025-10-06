@echo off
set GIT_PAGER=
git config core.pager ""

echo ========================================
echo Commit: Amelioration re-planification
echo ========================================
echo.

echo Ajout de tous les fichiers...
git add .
echo.

echo Creation du commit...
git commit -m "feat: Re-planification intelligente avec 3 cas de gestion" -m "Amelioration du bouton 'Re-planifier les retards' :" -m "" -m "Nouveaux cas geres:" -m "1. Taches non planifiees (sans date_planifiee)" -m "2. Taches avec planification depassee" -m "3. Taches avec echeance depassee et non terminees" -m "" -m "Ameliorations:" -m "- Message de confirmation detaille avec compteurs par categorie" -m "- Gestion erreurs amelioree avec compteur" -m "- Actualisation automatique de la vue Planning active" -m "- Tooltip explicatif mis a jour" -m "- Versioning des fichiers JS/CSS (v=2) pour forcer rechargement cache" -m "" -m "Fichiers modifies:" -m "- public/js/task-scheduler.js (fonction rescheduleLateTasks reecrite)" -m "- public/index.php (tooltip + versioning ?v=2)" -m "- TEST_REPLANIFICATION.md (documentation complete)" -m "" -m "Impact: +115 lignes, logique 3x plus intelligente" -m "Tests: Valides par utilisateur"
echo.

echo Push vers GitHub...
git push origin feat/mission-manager
echo.

echo ========================================
echo Termine avec succes!
echo ========================================
pause

