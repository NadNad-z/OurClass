<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'class_id',
        'generated_by',
        'judul',
        'tipe',
        'file_path',
    ];

    // === Relationships ===

    public function classModel()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
