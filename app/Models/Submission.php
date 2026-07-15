<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    protected $fillable = [
        'task_id',
        'user_id',
        'file',
        'catatan',
        'nilai',
        'feedback',
        'status',
        'submitted_at',
        'graded_at',
    ];

    protected $casts = [
        'nilai' => 'decimal:2',
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
    ];

    // === Relationships ===

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // === Helper Methods ===

    public function isGraded(): bool
    {
        return $this->status === 'graded';
    }

    public function isLate(): bool
    {
        return $this->status === 'late';
    }
}
