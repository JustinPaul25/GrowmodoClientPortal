<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectType extends Model
{
    use HasFactory;

    protected $hidden = [
        'created_at',
        'updated_at',
        'status',
        'pivot',
        'turn_around_days_from',
        'turn_around_days_to',
        'turn_around_days',
        'project_type',
        'filter',
    ];

    protected $appends = [
        'platforms',
        'tags',
        'questions'
    ];

    public function platforms()
    {
        return $this->belongsToMany(Platform::class, 'platform_project_types', 'project_type_id', 'platform_id')->withTimestamps();
    }

    public function categories()
    {
        return $this->belongsToMany(ProjectTypeCategory::class, 'pt_cats', 'project_type_id', 'category_id')->withTimestamps();
    }

    // public function categories() {
    //     return $this->belongsToMany(TaskTypeCategory::class, 'tt_cats', 'task_type_id', 'category_id')->withTimestamps();
    // }

    public function getQuestionsAttribute()
    {
        return $this->dynamicQuestions;
    }

    public function getPlatformsAttribute()
    {
        return $this->platforms()->get()->pluck('id');
    }

    public function getTagsAttribute()
    {
        return $this->categories()->get()->pluck('id');
    }

    public function getTurnArroundDaysAttribute()
    {
        return $this->turn_around_days_from . ' - ' . $this->turn_around_days_to . ' Days';
    }

    // public function getTypeAttribute()
    // {
    //     return $this->project_type;
    // }


    public function dynamicQuestions()
    {
        return $this->morphToMany(DynamicQuestion::class, 'dynamic_questionable');
    }
}
