<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $casts = [
        'dynamic_questions' => 'array',
    ];

    protected $appends = [
        'requestor'
    ];

    protected $with = [
        'uploads'
    ];

    public function getRequestorAttribute()
    {
        return User::find($this->requested_by_id);
    }

    public function organization() {
        return $this->belongsTo(Organization::class);
    }

    public function questions() {
        return $this->morphMany(DynamicQuestion::class, 'questionable');
    }

    public function uploads() {
        return $this->morphMany(Upload::class, 'uploadable');
    }
}
