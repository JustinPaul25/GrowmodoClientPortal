<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationSocialAccount extends Model
{
    use HasFactory;

    protected $hidden = [
        'organization_id',
        'created_at',
        'updated_at',
    ];

    public function organization() {
        return $this->belongsTo(Organization::class);
    }
}
