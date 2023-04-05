<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = [
        'subscription_billing_id',
        // 'subscription_talent_id',
        // 'talent',

    ];

    protected $hidden = [
        "subscription_plan_id",
        "subscription_plan_type_id",
    ];

    public function organization() {
        return $this->belongsTo(Organization::class);
    }

    public function plan() {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function plan_type() {
        return $this->belongsTo(SubscriptionPlanType::class);
    }

    public function talent() {
        return $this->belongsTo(SubscriptionTalent::class);
    }

    public function getSubscriptionBillingIdAttribute() {
        return $this->subscription_plan_type_id;
    }

    // public function getSubscriptionTalentIdAttribute() {
    //     return $this->subscription_talent_id;
    // }


    // public function getSubscriptionStartAttribute() {
    //     return $this->created_at;
    // }

    // public function getSubscriptionRenewedAttribute() {

    // }

    // public function getSubscriptionEndAttribute() {

    // }

}
