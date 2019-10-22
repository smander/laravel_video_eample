<?php

namespace App\Models\Traits;
use App\Observers\ModelObserver;

trait Changeable
{
    public static function bootChangeable()
    {
        static::observe(ModelObserver::class);
    }

}