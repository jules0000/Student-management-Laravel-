<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Http;

class Student extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'birthdate',
        'photo_url',
        'section',
        'address',
        'program_id',
        'email',
        'password',
    ];

    protected $casts = [
        'birthdate' => 'date',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Instructors who advise this student.
     * Stored in `adviser_assignments`.
     */
    public function advisers(): BelongsToMany
    {
        return $this->belongsToMany(Instructor::class, 'adviser_assignments', 'student_id', 'instructor_id')
            ->withTimestamps();
    }

    /**
     * Term-wise grades for this student.
     */
    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    /**
     * Raw bytes of the profile photo for the Python DeepFace service (data URL, HTTP(S), or /storage/...).
     */
    public function referencePhotoBinary(): ?string
    {
        $url = $this->photo_url;
        if ($url === null || trim($url) === '') {
            return null;
        }

        $url = trim($url);

        if (str_starts_with($url, 'data:')) {
            if (preg_match('#^data:image/[^;]+;base64,(.+)$#', $url, $m)) {
                $raw = base64_decode($m[1], true);

                return $raw !== false ? $raw : null;
            }

            return null;
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            try {
                $response = Http::timeout(15)->get($url);

                return $response->successful() ? $response->body() : null;
            } catch (\Throwable) {
                return null;
            }
        }

        $path = parse_url($url, PHP_URL_PATH);
        if (is_string($path) && str_starts_with($path, '/storage/')) {
            $rel = substr($path, strlen('/storage/'));
            $full = storage_path('app/public/'.$rel);
            if (is_file($full)) {
                $contents = @file_get_contents($full);

                return $contents !== false ? $contents : null;
            }
        }

        return null;
    }
}

