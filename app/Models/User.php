<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Exact table name in database.
     */
    protected $table = 'usuarios';

    /**
     * Primary key.
     */
    protected $primaryKey = 'id';

    /**
     * Disable Laravel automatic timestamps (created_at, updated_at) if table doesn't have them.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'correo',
        'contrasena',
        'area',
        'isAdmin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'contrasena',
    ];

    /**
     * Get the password for authentication.
     */
    public function getAuthPassword(): string
    {
        return $this->contrasena;
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     */
    public function getJWTCustomClaims(): array
    {
        return [
            'correo' => $this->correo,
            'nombre' => $this->nombre,
            'area' => $this->area,
            'isAdmin' => (bool) $this->isAdmin,
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
            'contrasena' => 'hashed',
            'isAdmin' => 'boolean',
        ];
    }

    /**
     * Relationship with Reportes.
     */
    public function reportes(): HasMany
    {
        return $this->hasMany(Reporte::class, 'idUser', 'id');
    }

    /**
     * Relationship with ControlSalidas.
     */
    public function salidas(): HasMany
    {
        return $this->hasMany(ControlSalida::class, 'idUser', 'id');
    }
}
