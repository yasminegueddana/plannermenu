<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;



Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/recipes/{id}', [HomeController::class, 'show'])->name('recipes.show');




Route::get('dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'active', 'role:admin'])
    ->name('dashboard');


Route::view('profile', 'profile')
    ->middleware(['auth', 'active'])
    ->name('profile');

require __DIR__.'/auth.php';

// Routes pour la gestion des ingrédients et recettes (protégées par authentification)
Route::middleware(['auth', 'verified', 'active'])->group(function () {

    // Routes pour tous les utilisateurs authentifiés
    Route::get('/ingredients', function () {
        return view('ingredients.index');
    })->name('ingredients.index');

    Route::get('/recipes', function () {
        return view('recipes.index');
    })->name('recipes.index');

    Route::get('/menus', function () {
        return view('menus.planner');
    })->name('menus.planner');

    Route::get('/menu-planner', function () {
        return view('menus.planner');
    })->name('menu-planner');

    Route::get('/shopping-list', function () {
        return view('shopping.recipe-generator');
    })->name('shopping-list.index');

    // Routes pour les exports de recettes
    Route::get('/recipe-shopping/export/pdf', [\App\Http\Controllers\RecipeShoppingController::class, 'exportPdf'])
        ->name('recipe-shopping.export.pdf');

    Route::get('/recipe-shopping/export/excel', [\App\Http\Controllers\RecipeShoppingController::class, 'exportExcel'])
        ->name('recipe-shopping.export.excel');


        // Exports depuis le planificateur de menus
    Route::get('/shopping-list/export/pdf', [\App\Http\Controllers\ShoppingListController::class, 'exportPdf'])
        ->name('shopping-list.export.pdf');
    Route::get('/shopping-list/export/excel', [\App\Http\Controllers\ShoppingListController::class, 'exportExcel'])
        ->name('shopping-list.export.excel');

  
    // Routes pour les administrateurs seulement
    Route::middleware(['role:admin', 'active'])->group(function () {
        Route::get('/admin/users', function () {
            return view('admin.users');
        })->name('admin.users');

        Route::get('/admin/settings', function () {
            return view('admin.settings');
        })->name('admin.settings');
    });
});
