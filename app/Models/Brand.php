<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Brand extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $with = [
        'uploads'
    ];

    protected $appends = [
        'folders',
    ];

    protected $casts = [
        'social_accounts' => 'array',
        'brand_colors' => 'array',
        'googlefonts' => 'array',
    ];

    public function getFoldersAttribute()
    {
        return GoogleDriveFolder::find($this->folder_id);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function uploads()
    {
        return $this->morphMany(Upload::class, 'uploadable');
    }
}
