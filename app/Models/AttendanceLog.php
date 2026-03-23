<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AttendanceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_session_id',
        'student_id',
        'marked_at',
        'geolocation_lat',
        'geolocation_lng',
        'distance_m',
        'geofence_radius_m',
        'face_detected',
        'face_image_path',
        'face_match_distance',
        'location_match',
        'status',
    ];

    protected $casts = [
        'marked_at' => 'datetime',
        'geolocation_lat' => 'decimal:7',
        'geolocation_lng' => 'decimal:7',
        'distance_m' => 'decimal:2',
        'face_match_distance' => 'decimal:6',
        'geofence_radius_m' => 'integer',
        'face_detected' => 'boolean',
        'location_match' => 'boolean',
    ];

    public function classSession(): BelongsTo
    {
        return $this->belongsTo(ClassSession::class, 'class_session_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    protected function faceImageUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (! $this->face_image_path) {
                return null;
            }

            return Storage::disk('public')->url($this->face_image_path);
        });
    }
}

