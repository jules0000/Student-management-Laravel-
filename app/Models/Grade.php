<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'subject_id',
        'term_id',
        'prelim',
        'midterm',
        'final_exam',
        'grade',
        'remarks',
        'status',
        'approved_by_program_chair_id',
        'approved_at',
    ];

    protected $casts = [
        'prelim' => 'decimal:2',
        'midterm' => 'decimal:2',
        'final_exam' => 'decimal:2',
        'grade' => 'decimal:2',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    /**
     * Whether this grade row is visible to the student (published after chair approval when applicable).
     */
    public function isPublishedToStudent(): bool
    {
        $hasStatus = Schema::hasColumn('grades', 'status');
        $hasApprovedAt = Schema::hasColumn('grades', 'approved_at');
        $hasApprovedBy = Schema::hasColumn('grades', 'approved_by_program_chair_id');

        if (! $hasStatus && ! $hasApprovedAt && ! $hasApprovedBy) {
            return true;
        }

        if ($hasStatus) {
            $s = strtolower(trim((string) $this->getAttribute('status')));
            if ($s === 'approved') {
                return true;
            }
        }

        if ($hasApprovedBy && $this->getAttribute('approved_by_program_chair_id') !== null) {
            return true;
        }

        if ($hasApprovedAt && $this->getAttribute('approved_at') !== null) {
            return true;
        }

        return false;
    }
}

