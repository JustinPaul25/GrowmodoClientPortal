<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    protected $with = [
        'brands', //'employees'
        'subscriptions',
        'uploads',
    ];

    protected $hidden = [
        'status'
    ];

    protected $casts = [
        'social_accounts' => 'array'
    ];

    protected $appends = [
        'base_folder',
    ];

    public function getBaseFolderAttribute()
    {
        return GoogleDriveFolder::find($this->folder_id);
    }

    public function owner()
    {
        return $this->belongsTo(User::class);
    }

    public function employees()
    {
        return $this->belongsToMany(User::class, 'organization_users', 'organization_id', 'user_id')->withTimestamps();
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function brands()
    {
        return $this->hasMany(Brand::class);
    }

    public function subscriptions()
    {
        return $this->hasOne(Subscription::class);
    }

    public function employee_count()
    {
        return $this->belongsTo(EmployeeCount::class);
    }

    public function uploads()
    {
        return $this->morphMany(Upload::class, 'uploadable');
    }
}
