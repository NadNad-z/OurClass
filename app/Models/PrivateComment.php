<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrivateComment extends Model
{
    protected $fillable = [
        'submission_id',
        'user_id',
        'komentar',
    ];

    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
