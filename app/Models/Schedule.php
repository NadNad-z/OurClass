<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'class_id',
        'mata_kuliah',
        'dosen',
        'hari',
        'waktu_mulai',
        'waktu_selesai',
        'ruangan',
        'catatan',
        'color',
    ];

    // === Relationships ===

    public function classModel()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function getFormattedStartTimeAttribute()
    {
        $time = \Carbon\Carbon::parse($this->waktu_mulai);
        return app()->getLocale() == 'en' ? $time->format('h:i A') : $time->format('H.i');
    }

    public function getFormattedEndTimeAttribute()
    {
        $time = \Carbon\Carbon::parse($this->waktu_selesai);
        return app()->getLocale() == 'en' ? $time->format('h:i A') : $time->format('H.i');
    }
}
