<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Ingredient extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'unite',
        'user_id',
    ];

    /**
     * Créer un ingrédient en évitant les doublons
     */
    public static function createUnique(array $data)
    {
        $existing = self::whereRaw('LOWER(name) = ?', [strtolower($data['name'])])
                       ->where('user_id', $data['user_id'] ?? null)
                       ->first();

        if ($existing) {
            return $existing;
        }

        return self::create($data);
    }

    /**
     * Rechercher un ingrédient par nom 
     */
    public static function findByName(string $name, $userId = null)
    {
        return self::whereRaw('LOWER(name) = ?', [strtolower($name)])
                  ->where('user_id', $userId)
                  ->first();
    }

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    /**
     * Relation many-to-many avec Recipe via RecipeIngredient
     */
    public function recipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'recipe_ingredient')
                    ->withPivot('quantity', 'unit')
                    ->withTimestamps();
    }

    /**
     * Relation avec RecipeIngredient (pour accès direct aux quantités)
     */
    public function recipeIngredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class);
    }

    /**
     * Relation avec l'utilisateur qui a créé l'ingrédient
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Vérifier si l'ingrédient est global (créé par un admin)
     */
    public function isGlobal(): bool
    {
        return $this->user_id === null || $this->user?->isAdmin();
    }

    /**
     * Vérifier si l'utilisateur peut modifier cet ingrédient
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

        // Utilisateur peut modifier ses propres ingrédients
        return $this->user_id === $user->id;
    }

    /**
     * Vérifier si l'utilisateur peut supprimer cet ingrédient
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
