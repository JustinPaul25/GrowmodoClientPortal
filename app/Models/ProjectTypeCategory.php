<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectTypeCategory extends Model
{
    use HasFactory;

    protected $hidden = [
        'created_at', 'updated_at', 'status', 'pivot', 'slug',
        'filter'
    ];

    protected $appends = [
        'tag',
    ];

    public function categories() {
        return $this->belongsToMany(ProjectType::class, 'pt_cats', 'category_id', 'project_type_id')->withTimestamps();
    }

    public function getTagAttribute() {
        return $this->filter;
    }

}
