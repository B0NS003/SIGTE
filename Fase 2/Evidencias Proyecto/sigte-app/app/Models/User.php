<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_TECNICO = 'tecnico';
    public const ROLE_JEFATURA = 'jefatura';
    public const ROLE_SECRETARIA = 'secretaria';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
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
        ];
    }

    public function isTecnico(): bool
    {
        return $this->role === self::ROLE_TECNICO;
    }

    public function isJefatura(): bool
    {
        return $this->role === self::ROLE_JEFATURA;
    }
}