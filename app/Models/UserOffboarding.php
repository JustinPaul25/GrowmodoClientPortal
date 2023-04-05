<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserOffboarding extends Model
{
    use HasFactory;

    protected $hidden = [
        'updated_at',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}