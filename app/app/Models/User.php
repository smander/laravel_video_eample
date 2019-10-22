<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, BelongsToMany, HasMany, HasManyThrough};
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens,
        Notifiable;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    //Boot Events
    public static function boot()
    {
        parent::boot();
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $dates = ['deleted_at'];

    protected $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email',
        'password',
        'username',
        'name'
    ];

    public function videos(): HasMany
    {
        return $this->hasMany('App\Media', 'model_id', 'id');
    }


    public function videosCount()
    {
        return $this->videos()
            ->selectRaw('model_id, sum(size) as aggregate')
            ->groupBy('model_id');
    }


}
