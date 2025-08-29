<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'preferences',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'preferences' => 'array',
            'is_active' => 'boolean',
        ];
    }

    // Constantes pour les rôles
    const ROLE_ADMIN = 'admin';
    const ROLE_USER = 'user';

    /**
     * Vérifier si l'utilisateur a un rôle spécifique
     */
    public function hasRole($role): bool
    {
        return $this->role === $role;
    }

    /**
     * Vérifier si l'utilisateur est admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    /**
     * Vérifier si l'utilisateur est un utilisateur standard
     */
    public function isUser(): bool
    {
        return $this->hasRole(self::ROLE_USER);
    }

    /**
     * Vérifier si l'utilisateur est actif
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Relation avec les recettes créées par l'utilisateur
     */
    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class);
    }

    /**
     * Relation avec les menus de l'utilisateur
     */
    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }

    /**
     * Relation avec les day menus de l'utilisateur
     */
    public function dayMenus(): HasMany
    {
        return $this->hasMany(DayMenu::class);
    }

    /**
     * Relation avec les feedbacks de l'utilisateur
     */
    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }
}

