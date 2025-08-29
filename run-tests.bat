@echo off
echo ========================================
echo    TESTS MENUPLANNER - PHASE DE TEST
echo ========================================
echo.

echo [1/5] Tests d'Authentification...
php artisan test tests/Feature/AuthenticationTest.php --verbose
echo.

echo [2/5] Tests d'Inscription...
php artisan test tests/Feature/RegistrationTest.php --verbose
echo.

echo [3/5] Tests de Gestion Utilisateurs...
php artisan test tests/Feature/UserManagementTest.php --verbose
echo.

echo [4/5] Tests de Gestion Ingredients...
php artisan test tests/Feature/IngredientManagementTest.php --verbose
echo.

echo [5/5] Tests de Gestion Recettes...
php artisan test tests/Feature/RecipeManagementTest.php --verbose
echo.

echo [6/6] Tests de Planification Menus...
php artisan test tests/Feature/MenuPlanningTest.php --verbose
echo.

echo [7/7] Tests de Liste de Courses...
php artisan test tests/Feature/ShoppingListTest.php --verbose
echo.

echo ========================================
echo    TESTS COMPLETS - RESUME
echo ========================================
php artisan test --coverage
echo.

echo Tests termines ! Verifiez les resultats ci-dessus.
pause
