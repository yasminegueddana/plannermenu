<?php

namespace App\Livewire;

use App\Models\Ingredient;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class IngredientManager extends Component
{
    use WithPagination;

    public $name = '';
    public $unite = '';
    public $search = '';
    public $editingId = null;
    public $showModal = false;
    public $isGlobal = false; // Pour les admins

    protected $rules = [
        'name' => 'required|string|max:255',
        'unite' => 'required|string|max:50',
    ];

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function store()
    {
        $this->validate();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        Ingredient::create([
            'name' => $this->name,
            'unite' => $this->unite,
            'user_id' => $this->isGlobal && $user?->isAdmin() ? null : Auth::id(),
        ]);

        $this->resetForm();
        $this->showModal = false;
        session()->flash('message', 'Ingrédient créé avec succès !');
    }

    public function edit($id)
    {
        $ingredient = Ingredient::findOrFail($id);

        // Vérifier les permissions
        if (!$ingredient->canEdit()) {
            session()->flash('error', 'Vous n\'avez pas le droit de modifier cet ingrédient.');
            return;
        }

        $this->editingId = $id;
        $this->name = $ingredient->name;
        $this->unite = $ingredient->unite;
        $this->showModal = true;
    }

    public function update()
    {
        $this->validate();

        $ingredient = Ingredient::findOrFail($this->editingId);

        // Vérifier les permissions
        if (!$ingredient->canEdit()) {
            session()->flash('error', 'Vous n\'avez pas le droit de modifier cet ingrédient.');
            $this->closeModal();
            return;
        }

        $ingredient->update([
            'name' => $this->name,
            'unite' => $this->unite,
        ]);

        $this->resetForm();
        $this->showModal = false;
        session()->flash('message', 'Ingrédient modifié avec succès !');
    }

    public function delete($id)
    {
        $ingredient = Ingredient::findOrFail($id);

        // Vérifier les permissions
        if (!$ingredient->canDelete()) {
            session()->flash('error', 'Vous n\'avez pas le droit de supprimer cet ingrédient.');
            return;
        }

        $ingredient->delete();
        session()->flash('message', 'Ingrédient supprimé avec succès !');
    }

    public function resetForm()
    {
        $this->name = '';
        $this->unite = '';
        $this->editingId = null;
        $this->isGlobal = false;
        $this->resetValidation();
    }

    public function closeModal()
    {
        $this->resetForm();
        $this->showModal = false;
    }

    public function render()
    {
        $ingredients = Ingredient::where('name', 'like', '%' . $this->search . '%')
                                ->with('user')
                                ->paginate(10);

        return view('livewire.ingredient-manager', [
            'ingredients' => $ingredients
        ]);
    }
}







