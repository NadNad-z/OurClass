<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'judul',
        'deskripsi',
        'class_id',
        'created_by',
        'deadline',
        'file_soal',
        'tipe',
        'nilai_max',
        'status',
    ];

    protected $casts = [
        'deadline' => 'datetime',
    ];

    // === Relationships ===

    public function classModel()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    // === Helper Methods ===

    public function isOverdue(): bool
    {
        return now()->isAfter($this->deadline);
    }

    public function getSubmissionCountAttribute(): int
    {
        return $this->submissions()->count();
    }

    public function getGradedCountAttribute(): int
    {
        return $this->submissions()->where('status', 'graded')->count();
    }
}
