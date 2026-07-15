<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'phone',
        'nim_nip',
        'theme_mode',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // === Relationships ===

    /** Kelas yang dibuat user (sebagai admin utama) */
    public function ownedClasses()
    {
        return $this->hasMany(ClassModel::class, 'admin_id');
    }

    /** Kelas yang diikuti user */
    public function classes()
    {
        return $this->belongsToMany(ClassModel::class, 'class_user', 'user_id', 'class_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    /** Submissions milik user */
    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    public function privateComments()
    {
        return $this->hasMany(PrivateComment::class);
    }

    /** Diskusi yang dibuat user */
    public function discussions()
    {
        return $this->hasMany(Discussion::class);
    }

    /** Notifikasi milik user */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /** Notifikasi yang belum dibaca */
    public function unreadNotifications()
    {
        return $this->notifications()->where('is_read', false);
    }

    /** Activity logs milik user */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    // === Helper Methods ===

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isDosen(): bool
    {
        return $this->role === 'dosen';
    }

    public function isMahasiswa(): bool
    {
        return $this->role === 'mahasiswa';
    }

    /** Cek apakah user adalah admin dari kelas tertentu */
    public function isClassAdmin(ClassModel $class): bool
    {
        if ($this->id === $class->admin_id) {
            return true;
        }

        return $this->classes()
            ->where('class_id', $class->id)
            ->wherePivot('role', 'admin')
            ->exists();
    }
}
