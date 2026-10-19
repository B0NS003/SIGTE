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

    /** Cuenta compartida del computador del puesto. Desde ahí se elige qué operadora está registrando. */
    public const CUENTA_PUESTO = 'operador@sigte.local';

    public function esCuentaDelPuesto(): bool
    {
        return $this->email === self::CUENTA_PUESTO;
    }

    /** La operadora que queda en el registro: ella misma, o la elegida desde la cuenta del puesto. */
    public static function quienRegistra(): ?self
    {
        $actual = auth()->user();
        if (! $actual instanceof self || ! $actual->isOperador()) {
            return $actual instanceof self ? $actual : null;
        }
        if (! $actual->esCuentaDelPuesto()) {
            return $actual;
        }
        $id = session('puesto_user_id');
        if ($id === null) {
            return null;
        }

        return static::query()
            ->where('activo', true)
            ->whereKey($id)
            ->whereHas('rol', fn ($q) => $q->where('nombre', Rol::OPERADOR))
            ->where('email', '!=', self::CUENTA_PUESTO)
            ->first();
    }
}
