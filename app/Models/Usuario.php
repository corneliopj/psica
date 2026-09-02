<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'nome',
        'name',
        'email',
        'password',
        'perfil',
        'status',
        'email_verificado_at',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verificado_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getNameAttribute(): string
    {
        return $this->attributes['nome'] ?? '';
    }

    public function setNameAttribute(string $value): void
    {
        $this->attributes['nome'] = $value;
    }

    public function getEmailVerifiedAtAttribute(): mixed
    {
        return $this->getAttribute('email_verificado_at');
    }

    public function setEmailVerifiedAtAttribute(mixed $value): void
    {
        $this->attributes['email_verificado_at'] = $value;
    }
}
