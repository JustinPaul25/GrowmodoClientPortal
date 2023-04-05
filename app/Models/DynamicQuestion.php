<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DynamicQuestion extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $hidden = [
        'created_at', 'updated_at',
    ];

    protected $casts = [
        'options' => 'array',
        'alternative_options' => 'array',
    ];

    public function questionable()
    {
        return $this->morphTo();
    }

    public function projects()
    {
        return $this->morphedByMany(ProjectType::class, 'dynamic_questionable');
    }

    public function taskTypes()
    {
        return $this->morphedByMany(TaskType::class, 'dynamic_questionable');
    }
}
