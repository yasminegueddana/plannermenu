<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Recipe extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'instructions',
        'prep_time',
        'cook_time',
        'servings',
        'image',
        'user_id',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    /**
     * Relation avec User (créateur de la recette)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation many-to-many avec Ingredient via RecipeIngredient
     */
    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'recipe_ingredient')
                    ->withPivot('quantity', 'unit')
                    ->withTimestamps();
    }

    /**
     * Relation many-to-many avec Menu via RecipeMenu
     */
    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'recipe_menu')
                    ->withTimestamps();
    }

    /**
     * Relation avec les feedbacks
     */
    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    /**
     * Relation avec RecipeIngredient (pour accès direct aux quantités)
     */
    public function recipeIngredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class);
    }

    /**
     * Vérifier si l'utilisateur peut modifier cette recette
     */
    public function canEdit(?User $user = null): bool
    {
        if (!$user) {
            /** @var User|null $user */
            $user = Auth::user();
        }

        if (!$user) {
            return false;
        }

        // Admin peut tout modifier
        if ($user->isAdmin()) {
            return true;
        }

        // Utilisateur peut modifier ses propres recettes
        return $this->user_id === $user->id;
    }

    /**
     * Vérifier si l'utilisateur peut supprimer cette recette
     */
    public function canDelete(?User $user = null): bool
    {
        return $this->canEdit($user);
    }

    /**
     * Obtenir le nom de l'auteur pour affichage
     */
    public function getAuthorNameAttribute(): string
    {
        if ($this->user_id === null) {
            return 'Système';
        }

        return $this->user?->name ?? 'Utilisateur supprimé';
    }
}
