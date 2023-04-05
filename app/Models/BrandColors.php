<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrandColors extends Model
{
    use HasFactory;

    protected $hidden = [
        'brand_id',
        'created_at',
        'updated_at',
    ];

    public function brand() {
        return $this->belongsTo(Brand::class);
    }
}
