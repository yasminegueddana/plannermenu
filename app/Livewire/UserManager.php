<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Recipe;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class UserManager extends Component
{
    use WithPagination;

    public $showEditModal = false;
    public $showDeleteModal = false;
    public $editingUser = null;
    public $deletingUser = null;

    // Propriétés pour l'édition
    public $editName = '';
    public $editEmail = '';
    public $editRole = '';
    public $editIsActive = true;

    protected $rules = [
        'editName' => 'required|string|max:255',
        'editEmail' => 'required|email|max:255',
        'editRole' => 'required|in:admin,user',
        'editIsActive' => 'boolean',
    ];

    protected $messages = [
        'editName.required' => 'Le nom est obligatoire.',
        'editEmail.required' => 'L\'email est obligatoire.',
        'editEmail.email' => 'L\'email doit être valide.',
        'editRole.required' => 'Le rôle est obligatoire.',
        'editRole.in' => 'Le rôle doit être admin ou user.',
    ];

    public function openEditModal($userId)
    {
        $this->editingUser = User::findOrFail($userId);
        $this->editName = $this->editingUser->name;
        $this->editEmail = $this->editingUser->email;
        $this->editRole = $this->editingUser->role;
        $this->editIsActive = $this->editingUser->is_active;
        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->editingUser = null;
        $this->reset(['editName', 'editEmail', 'editRole', 'editIsActive']);
        $this->resetErrorBag();
    }

    public function updateUser()
    {
        $this->validate();

        // Vérifier que l'email n'est pas déjà utilisé par un autre utilisateur
        $existingUser = User::where('email', $this->editEmail)
                           ->where('id', '!=', $this->editingUser->id)
                           ->first();

        if ($existingUser) {
            $this->addError('editEmail', 'Cet email est déjà utilisé par un autre utilisateur.');
            return;
        }

        $this->editingUser->update([
            'name' => $this->editName,
            'email' => $this->editEmail,
            'role' => $this->editRole,
            'is_active' => $this->editIsActive,
        ]);

        session()->flash('message', 'Utilisateur mis à jour avec succès.');
        $this->closeEditModal();
    }

    public function toggleUserStatus($userId)
    {
        $user = User::findOrFail($userId);
        $currentUser = Auth::user();

        // Empêcher la désactivation de son propre compte
        if ($currentUser && $user->id === $currentUser->id) {
            session()->flash('error', 'Vous ne pouvez pas modifier votre propre statut.');
            return;
        }

        $user->update([
            'is_active' => !$user->is_active
        ]);

        $status = $user->is_active ? 'activé' : 'désactivé';
        session()->flash('message', "Utilisateur {$status} avec succès.");
    }

    public function openDeleteModal($userId)
    {
        $this->deletingUser = User::findOrFail($userId);
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deletingUser = null;
    }

    public function deleteUser()
    {
        if (!$this->deletingUser) {
            return;
        }

        // Empêcher la suppression de son propre compte
        $currentUser = Auth::user();
        if ($currentUser && $this->deletingUser->id === $currentUser->id) {
            session()->flash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
            $this->closeDeleteModal();
            return;
        }

        // Supprimer l'utilisateur (soft delete si configuré)
        $this->deletingUser->delete();

        session()->flash('message', 'Utilisateur supprimé avec succès.');
        $this->closeDeleteModal();
    }

    public function render()
    {
        $users = User::orderBy('created_at', 'desc')->get();
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();
        $desactiveUsers = User::where('is_active', false)->count();
        //$totalRecipes = Recipe::count();

        return view('livewire.user-manager', [
            'users' => $users,
            'totalUsers' => $totalUsers,
            'activeUsers' => $activeUsers,
            'desactiveUsers' => $desactiveUsers,
           // 'totalRecipes' => $totalRecipes,
        ]);
    }
}
