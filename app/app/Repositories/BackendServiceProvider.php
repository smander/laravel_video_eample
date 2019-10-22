<?php
/**
 * Created by PhpStorm.
 * User: smander
 * Date: 2019-10-22
 * Time: 12:13
 */

namespace App\Repositories;

use Illuminate\Support\ServiceProvider;

class BackendServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->bind(
            'App\Repositories\MediaRepositoryInterface',
            'App\Repositories\MediaRepository'
        );

    }
}
