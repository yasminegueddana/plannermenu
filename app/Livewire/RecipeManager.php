<?php

namespace App\Livewire;

use App\Models\Recipe;
use App\Models\Ingredient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class RecipeManager extends Component
{
    use WithPagination, WithFileUploads;

    public $name = '';
    public $description = '';
    public $instructions = '';
    public $image = '';
    public $imageFile = null;
    public $search = '';
    public $ingredientSearch = ''; // Recherche pour les ingrédients
    public $editingId = null;
    public $showModal = false;
    public $showDetailModal = false;
    public $selectedRecipe = null;

    public $selectedIngredients = [];
    public $ingredientQuantities = [];
    public $ingredientUnits = [];

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'imageFile' => 'nullable|image|max:2048', // 2MB max
    ];

    protected function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'imageFile' => 'nullable|image|max:2048',
        ];

        // Image obligatoire seulement pour la création
        if (!$this->editingId) {
            $rules['imageFile'] = 'required|image|max:2048';
        }

        return $rules;
    }

    public function mount()
    {
        $this->selectedIngredients = [];
    }

    /**
     * Méthode appelée automatiquement quand selectedIngredients change
     * Initialise les quantités et unités par défaut pour les nouveaux ingrédients
     */
    public function updatedSelectedIngredients()
    {
        foreach ($this->selectedIngredients as $ingredientId) {
            // Initialiser la quantité par défaut si elle n'existe pas
            if (!isset($this->ingredientQuantities[$ingredientId])) {
                $this->ingredientQuantities[$ingredientId] = 1;
            }

            // Initialiser l'unité par défaut si elle n'existe pas
            if (!isset($this->ingredientUnits[$ingredientId])) {
                // Récupérer l'unité par défaut de l'ingrédient
                $ingredient = \App\Models\Ingredient::find($ingredientId);
                $this->ingredientUnits[$ingredientId] = $ingredient ? $ingredient->unite : 'g';
            }
        }

        // Nettoyer les quantités et unités des ingrédients désélectionnés
        $this->ingredientQuantities = array_intersect_key($this->ingredientQuantities, array_flip($this->selectedIngredients));
        $this->ingredientUnits = array_intersect_key($this->ingredientUnits, array_flip($this->selectedIngredients));
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function store()
    {
        $this->validate();

        // Upload de l'image
        $imagePath = null;
        if ($this->imageFile) {
            $imagePath = $this->imageFile->store('recipes', 'public');
        }

        $recipe = Recipe::create([
            'name' => $this->name,
            'description' => $this->description,
            'instructions' => $this->instructions,
            'image' => $imagePath,
            'user_id' => Auth::id(),
        ]);

        // Attacher les ingrédients sélectionnés
        $this->attachIngredients($recipe);

        $this->resetForm();
        $this->showModal = false;
        session()->flash('message', 'Recette créée avec succès !');
    }

    public function edit($id)
    {
        $recipe = Recipe::with('ingredients')->findOrFail($id);

        // Vérifier les permissions
        if (!$recipe->canEdit()) {
            session()->flash('error', 'Vous n\'avez pas le droit de modifier cette recette.');
            return;
        }

        $this->editingId = $id;
        $this->name = $recipe->name;
        $this->description = $recipe->description;
        $this->instructions = $recipe->instructions;
        $this->image = $recipe->image;
        $this->imageFile = null; // Reset file input

        // Charger les ingrédients existants
        $this->selectedIngredients = $recipe->ingredients->pluck('id')->toArray();
        foreach ($recipe->ingredients as $ingredient) {
            $this->ingredientQuantities[$ingredient->id] = $ingredient->pivot->quantity;
            $this->ingredientUnits[$ingredient->id] = $ingredient->pivot->unit;
        }

        $this->showModal = true;
    }

    public function update()
    {
        $this->validate();

        $recipe = Recipe::findOrFail($this->editingId);

        // Vérifier les permissions
        if (!$recipe->canEdit()) {
            session()->flash('error', 'Vous n\'avez pas le droit de modifier cette recette.');
            $this->closeModal();
            return;
        }

        // Gérer l'upload de la nouvelle image
        $imagePath = $recipe->image; // Garder l'ancienne image par défaut
        if ($this->imageFile) {
            // Supprimer l'ancienne image si elle existe
            if ($recipe->image && Storage::disk('public')->exists($recipe->image)) {
                Storage::disk('public')->delete($recipe->image);
            }
            // Uploader la nouvelle image
            $imagePath = $this->imageFile->store('recipes', 'public');
        }

        $recipe->update([
            'name' => $this->name,
            'description' => $this->description,
            'instructions' => $this->instructions,
            'image' => $imagePath,
        ]);


        $recipe->ingredients()->detach();
        $this->attachIngredients($recipe);

        $this->resetForm();
        $this->showModal = false;
        session()->flash('message', 'Recette modifiée avec succès !');
    }

    public function delete($id)
    {
        $recipe = Recipe::findOrFail($id);

        // Vérifier les permissions
        if (!$recipe->canDelete()) {
            session()->flash('error', 'Vous n\'avez pas le droit de supprimer cette recette.');
            return;
        }

        $recipe->delete();
        session()->flash('message', 'Recette supprimée avec succès !');
    }

    private function attachIngredients(Recipe $recipe)
    {
        foreach ($this->selectedIngredients as $ingredientId) {
            // Utilise la quantité saisie ou 1 par défaut si aucune quantité n'est spécifiée
            $quantity = isset($this->ingredientQuantities[$ingredientId]) && $this->ingredientQuantities[$ingredientId] > 0
                ? $this->ingredientQuantities[$ingredientId]
                : 1;

            $recipe->ingredients()->attach($ingredientId, [
                'quantity' => $quantity,
                'unit' => $this->ingredientUnits[$ingredientId] ?? 'g',
            ]);
        }
    }

    public function resetForm()
    {
        $this->name = '';
        $this->description = '';
        $this->instructions = '';
        $this->image = '';
        $this->imageFile = null;
        $this->editingId = null;
        $this->selectedIngredients = [];
        $this->ingredientQuantities = [];
        $this->ingredientUnits = [];
        $this->ingredientSearch = ''; // Réinitialiser la recherche d'ingrédients
        $this->resetValidation();
    }

    public function closeModal()
    {
        $this->resetForm();
        $this->showModal = false;
    }

    public function showRecipeDetails($id)
    {
        $this->selectedRecipe = Recipe::with('ingredients', 'user')->findOrFail($id);
        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedRecipe = null;
    }

    public function render()
    {
        $recipes = Recipe::with('ingredients', 'user')
                        ->where('name', 'like', '%' . $this->search . '%')
                        ->paginate(10);

        // Filtrer les ingrédients selon la recherche
        $ingredientsQuery = Ingredient::query();

        if (!empty($this->ingredientSearch)) {
            $ingredientsQuery->where('name', 'like', '%' . $this->ingredientSearch . '%');
        }

        $ingredients = $ingredientsQuery->get();

        return view('livewire.recipe-manager', [
            'recipes' => $recipes,
            'ingredients' => $ingredients
        ]);
    }
}
