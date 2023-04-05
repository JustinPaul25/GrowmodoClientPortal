<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $with = [
        'uploads'
    ];

    protected $casts = [
        'dynamic_questions' => 'array',
    ];

    protected $appends = [
        'requestor'
    ];

    public function getRequestorAttribute()
    {
        return User::find($this->requested_by_id);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function questions()
    {
        return $this->morphMany(DynamicQuestion::class, 'dynamic_questionable');
    }

    public function uploads()
    {
        return $this->morphMany(Upload::class, 'uploadable');
    }
}
