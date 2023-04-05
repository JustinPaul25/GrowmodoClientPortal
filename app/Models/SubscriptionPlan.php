<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $with = [
        'talent',
        'type',
    ];

    public function talent()
    {
        return $this->belongsTo(SubscriptionTalent::class, 'talent_id');
    }

    public function type()
    {
        return $this->belongsTo(SubscriptionPlanType::class, 'plan_type_id');
    }
}
