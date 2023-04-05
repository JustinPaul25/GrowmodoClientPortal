<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskTypeCategory extends Model
{
    use HasFactory;

    protected $hidden = [
        'created_at', 'updated_at', 'status', 'pivot', 'slug', 'filter'
    ];

    protected $appends = [
        'tag'
    ];

    public function categories() {
        return $this->belongsToMany(TaskType::class, 'tt_cats', 'category_id', 'task_type_id')->withTimestamps();
    }

    public function getTagAttribute() {
        return \Str::slug( strtolower($this->title) );
    }

}
