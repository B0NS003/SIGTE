<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'rol_id',
        'activo',
        'role', // columna legacy; se mantiene hasta limpiar migración antigua
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function nombreRol(): ?string
    {
        return $this->rol?->nombre;
    }

    public function hasRole(string $nombre): bool
    {
        return $this->nombreRol() === $nombre;
    }

    public function isAdministradora(): bool
    {
        return $this->hasRole(Rol::ADMINISTRADORA);
    }

    public function isEnfermera(): bool
    {
        return $this->hasRole(Rol::ENFERMERA);
    }

    public function isOperador(): bool
    {
        return $this->hasRole(Rol::OPERADOR);
    }

    public function isSecretaria(): bool
    {
        return $this->hasRole(Rol::SECRETARIA);
    }

    public function isActivo(): bool
    {
        return (bool) $this->activo;
    }
}
