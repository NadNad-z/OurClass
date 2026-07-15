<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ClassModel extends Model
{
    protected $table = 'classes';

    protected $fillable = [
        'nama_kelas',
        'kode_unik',
        'deskripsi',
        'mata_kuliah',
        'ruangan',
        'semester',
        'tahun_ajaran',
        'cover_image',
        'color',
        'admin_id',
        'is_active',
        'is_private',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_private' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        // Auto-generate kode unik saat membuat kelas baru
        static::creating(function ($class) {
            if (empty($class->kode_unik)) {
                $class->kode_unik = strtoupper(Str::random(6));
            }
        });
    }

    // === Relationships ===

    /** Admin utama kelas */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /** Semua anggota kelas (termasuk admin) */
    public function members()
    {
        return $this->belongsToMany(User::class, 'class_user', 'class_id', 'user_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    /** Hanya anggota biasa (bukan admin) */
    public function students()
    {
        return $this->members()->wherePivot('role', 'member');
    }

    /** Admin kelas (multi-admin) */
    public function admins()
    {
        return $this->members()->wherePivot('role', 'admin');
    }

    /** Tugas dalam kelas */
    public function tasks()
    {
        return $this->hasMany(Task::class, 'class_id');
    }

    /** Jadwal kelas */
    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'class_id');
    }

    /** Diskusi kelas */
    public function discussions()
    {
        return $this->hasMany(Discussion::class, 'class_id');
    }

    /** Notifikasi kelas */
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'class_id');
    }

    /** Laporan kelas */
    public function reports()
    {
        return $this->hasMany(Report::class, 'class_id');
    }

    // === Helper Methods ===

    /** Hitung jumlah anggota */
    public function getMemberCountAttribute(): int
    {
        return $this->members()->count();
    }

    /** Hitung jumlah tugas aktif */
    public function getActiveTaskCountAttribute(): int
    {
        return $this->tasks()->where('status', 'published')->count();
    }
}
