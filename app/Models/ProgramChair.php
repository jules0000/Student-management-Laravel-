<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProgramChair extends Authenticatable
{
    use HasFactory;

    protected $table = 'program_chairs';

    protected $fillable = [
        'email',
        'password',
        'name',
        'photo_url',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function programs(): BelongsToMany
    {
        return $this->belongsToMany(Program::class, 'program_chair_programs', 'program_chair_id', 'program_id')
            ->withTimestamps();
    }
}

