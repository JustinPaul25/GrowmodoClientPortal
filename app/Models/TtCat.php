<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TtCat extends Model
{
    use HasFactory;


    public function task_types() {
        return $this->belongsToMany(TaskType::class, 'tt_cats', 'ttcat_id')->withTimestamps();
    }

}
