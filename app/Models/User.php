<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;  

class User extends Authenticatable implements JWTSubject  
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'prenom',
        'email',
        'password',
        'phone',
        'address',
        'role',
        'image',
        'code_parrainage',  // Code de parrainage unique
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
     * Get the identifier that will be stored in the JWT token.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();  // Retourne l'ID de l'utilisateur (clé primaire)
    }

    /**
     * Get the custom claims for the JWT token.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'role' => $this->role,  // Ajout du rôle dans le token JWT
        ];
    }

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
        ];
    }
    public function getImageUrlAttribute()
{
    return $this->image ? asset('storage/' . $this->image) : null;
}
public function formations()
{
    return $this->hasMany(Formation::class);
}

// Formations créées par un admin
public function formationsCreees()
{
    return $this->hasMany(Formation::class, 'user_id');
}

// Candidatures liées à ce parrain
public function candidaturesParrain()
{
    return $this->hasMany(Candidature::class, 'code_parrainage', 'code_parrainage');
}

// Dans User.php
public function actualites()
{
    return $this->hasMany(Actualite::class);
}
// Commissions qu’il a validées (admin)
    public function commissionsValidees()
    {
        return $this->hasMany(Commission::class, 'user_id');
    }

 }


