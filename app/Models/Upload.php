<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    use HasFactory;

    protected $fillable = [
        'uploadable_id',
        'uploader_id',
        'uploadable_type',
        'file',
        'path',
        'size',
    ];

    protected $with = [
        // 'uploader'
    ];

    protected $appends = ['readable_file_size'];

    //public function setFilenamesAttribute($value)
    //{
     //   $this->attributes['file'] = json_encode($value);
   // }

   public function getReadableFileSizeAttribute()
    {
        return convert_file_size($this->size);
    }

    public function uploadable() {
        return $this->morphTo();
    }

    public function uploader() {
        return $this->belongsTo(User::class);
    }

}
