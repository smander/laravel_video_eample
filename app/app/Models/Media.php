<?php

namespace App;
use Spatie\MediaLibrary\Models\Media as BaseMedia;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Media extends BaseMedia
{

    const collection_name = 'video';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
                'slug',
                'label',

    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    public function model(): MorphTo
    {
        return $this->morphTo();
    }



}
