<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Instructor extends Authenticatable
{
    use HasFactory;

    protected $table = 'instructors';

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

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class, 'instructor_id');
    }

    public function advisees(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'adviser_assignments', 'instructor_id', 'student_id')
            ->withTimestamps();
    }
}

