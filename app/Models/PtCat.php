<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PtCat extends Model
{
    use HasFactory;


    public function project_types() {
        return $this->belongsToMany(ProjectType::class, 'pt_cats', 'ptcat_id')->withTimestamps();
    }

}
