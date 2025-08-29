<?php

namespace App\Http\Controllers;

use App\Models\ShoppingList;
use App\Models\Menu;
use App\Exports\ShoppingListExport;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class ShoppingListController extends Controller
{
    public function exportPdf()
    {
        // Vérifier si on a des données en session (export depuis le planificateur)
        if (session()->has('menu_planner_export_data')) {
            $exportData = session()->get('menu_planner_export_data');
            session()->forget('menu_planner_export_data');
            
            $ingredientsList = $exportData['ingredients'];
            
            $pdf = Pdf::loadView('pdf.shopping-list', [
                'ingredientsList' => $ingredientsList,
                'menuName' => 'Planificateur de Menus',
                'date' => now(),
            ]);
            
            $filename = 'liste-courses-planificateur-' . now()->format('Y-m-d') . '.pdf';
            
            return $pdf->download($filename);
        }
        
        // Logique existante pour les exports depuis la base de données
        $menu = Menu::where('user_id', Auth::id())->latest()->first();

        if (!$menu) {
            return redirect()->back()->with('error', 'Aucun menu trouvé.');
        }

        $shoppingList = ShoppingList::where('menu_id', $menu->id)->with('items')->first();

        if (!$shoppingList || $shoppingList->items->isEmpty()) {
            return redirect()->back()->with('error', 'Aucune liste de courses trouvée. Générez d\'abord une liste.');
        }

        $ingredientsList = $shoppingList->items->map(function ($item) {
            return [
                'name' => $item->ingredient_name,
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'recipes' => $item->recipes,
                'is_purchased' => $item->is_purchased,
            ];
        })->toArray();

        $pdf = Pdf::loadView('pdf.shopping-list', [
            'ingredientsList' => $ingredientsList,
            'menuName' => $menu->name,
            'date' => $shoppingList->date,
        ]);

        $filename = 'liste-courses-' . str_replace(' ', '-', strtolower($menu->name)) . '-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    public function exportExcel()
    {
        // Vérifier si on a des données en session (export depuis le planificateur)
        if (session()->has('menu_planner_export_data')) {
            $exportData = session()->get('menu_planner_export_data');
            session()->forget('menu_planner_export_data');
            
            $ingredientsList = $exportData['ingredients'];
            
            $filename = 'liste-courses-planificateur-' . now()->format('Y-m-d') . '.csv';

            $export = new ShoppingListExport($ingredientsList, 'Planificateur de Menus', now());
            $csv = $export->generateCsv();

            return response($csv)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        }
        
        // Logique existante pour les exports depuis la base de données
        $menu = Menu::where('user_id', Auth::id())->latest()->first();

        if (!$menu) {
            return redirect()->back()->with('error', 'Aucun menu trouvé.');
        }

        $shoppingList = ShoppingList::where('menu_id', $menu->id)->with('items')->first();

        if (!$shoppingList || $shoppingList->items->isEmpty()) {
            return redirect()->back()->with('error', 'Aucune liste de courses trouvée. Générez d\'abord une liste.');
        }

        $ingredientsList = $shoppingList->items->map(function ($item) {
            return [
                'name' => $item->ingredient_name,
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'recipes' => $item->recipes,
                'is_purchased' => $item->is_purchased,
            ];
        })->toArray();

        $filename = 'liste-courses-' . str_replace(' ', '-', strtolower($menu->name)) . '-' . now()->format('Y-m-d') . '.csv';

        $export = new ShoppingListExport($ingredientsList, $menu->name, $shoppingList->date);
        $csv = $export->generateCsv();

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}