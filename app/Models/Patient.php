<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Prontuario;
use App\Models\Appointment;

class Patient extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'birth_date',
        'notes',
    ];

    public function prontuarios(): HasMany
    {
        return $this->hasMany(Prontuario::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
