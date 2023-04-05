<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlanType extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'value',
        'billed_label',
        'breakdown_label',
        'savings',
        'savings_percentage',
        'savings_per_month',
        'savings_label',
        'savings_currency',
        'savings_currency_legend',
        'interval',
        'interval_type',
    ];

    protected $hidden = [
        'status',
        'savings_currency_legend',
        'interval',
        'interval_type',
    ];

}
