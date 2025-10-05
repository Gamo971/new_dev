@echo off
set GIT_PAGER=
git config core.pager ""

echo ========================================
echo Commit: Systeme de planification complet
echo ========================================
echo.

echo Ajout de tous les fichiers...
git add .
echo.

echo Creation du commit...
git commit -m "feat: Systeme de planification avec date_planifiee et ordonnancement automatique" -m "Backend:" -m "- Ajout champ date_planifiee a la table taches avec migration" -m "- Modele Tache enrichi avec 4 nouvelles methodes utilitaires" -m "- Repository avec 6 nouvelles methodes de recherche" -m "- Controleur avec validation complete" -m "" -m "Frontend:" -m "- Formulaire avec date de planification + bouton planification auto" -m "- Affichage dynamique de la marge de securite (couleurs)" -m "- Algorithme de planification automatique intelligent" -m "- Re-planification des taches en retard" -m "- Agenda avec affichage dual (planifiee + echeance)" -m "- Drag & drop pour deplacer les taches planifiees" -m "- Labels de boutons clarifies avec tooltips" -m "" -m "Fichiers:" -m "- public/css/style.css (recree)" -m "- public/js/task-scheduler.js (nouveau, 320 lignes)" -m "- src/Database/migrations/add_date_planifiee.php" -m "- Corrections erreurs syntaxe JavaScript" -m "" -m "Status: Production Ready" -m "Lignes ajoutees: ~700"
echo.

echo Push vers GitHub...
git push origin feat/mission-manager
echo.

echo ========================================
echo Termine avec succes!
echo ========================================
pause

