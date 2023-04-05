<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Platform extends Model
{
    use HasFactory;

    protected $hidden = [
        'created_at', 'updated_at', 'description', 'status', 'image_url', 'pivot',
    ];

    public function task_types() {
        return $this->belongsToMany(TaskType::class, 'platform_task_types', 'platform_id', 'task_type_id')->withTimestamps();
    }

}
