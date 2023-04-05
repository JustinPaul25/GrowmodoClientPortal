<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use HasRoles;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'password',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'onboardings',
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
        'signup_token',
        'payment_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        // 'profile_photo_url',
    ];

    public function organization()
    {
        return $this->hasMany(Organization::class, 'owner_id');
    }

    public function employer()
    {
        return $this->belongsToMany(User::class, 'organization_users', 'user_id', 'organization_id')->withTimestamps();
    }

    public function onboardings()
    {
        return $this->hasMany(UserOnboarding::class);
    }

    public function offboardings()
    {
        return $this->hasMany(UserOffboarding::class);
    }

    public function getOrganizations()
    {
        // return $this->hasRole('organization_owner');
        if ($this->hasRole('organization_admin')) {
            return $this->organization;
        } else {
            return $this->employer;
        }
    }
}
