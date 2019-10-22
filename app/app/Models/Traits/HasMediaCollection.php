<?php

namespace App\Models\Traits;

use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

trait HasMediaCollection
{
    use HasMediaTrait;

    public function registerMediaCollections()
    {
        foreach (get_class_methods($this) as $method) {
            if (starts_with($method, 'hasMediaCollection')) {
                $this->{$method}();
            }
        }
    }
}
