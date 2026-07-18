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

    public function isGuru(): bool
    {
        return $this->role === 'guru';
    }

    public function isMahasiswa(): bool
    {
        return $this->role === 'mahasiswa';
    }

    public function isSiswa(): bool
    {
        return $this->role === 'siswa';
    }

    /** Cek apakah user adalah pengajar (dosen/guru) */
    public function isTeacher(): bool
    {
        return in_array($this->role, ['dosen', 'guru', 'admin']);
    }

    /** Cek apakah user adalah pelajar (mahasiswa/siswa) */
    public function isStudent(): bool
    {
        return in_array($this->role, ['mahasiswa', 'siswa']);
    }

    /** Label role yang tampil di UI */
    public function getRoleLabelAttribute(): string
    {
        return match($this->role) {
            'admin' => 'Admin',
            'dosen' => 'Dosen',
            'guru' => 'Guru',
            'mahasiswa' => 'Mahasiswa',
            'siswa' => 'Siswa',
            default => ucfirst($this->role),
        };
    }

    /** Mendapatkan URL Avatar (Lokal atau Default) */
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            if (\Illuminate\Support\Str::startsWith($this->avatar, 'http')) {
                return $this->avatar;
            }
            return asset('storage/' . $this->avatar);
        }
        
        return 'https://api.dicebear.com/7.x/adventurer/svg?seed=' . urlencode($this->name);
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
