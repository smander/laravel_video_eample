<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Laravel\Passport\Passport;
use App\User;
//use App\Observers\ElasticsearchObserver;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use App\Repositories\ElasticsearchUsersRepository;
use App\Repositories\UsersRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Passport::withoutCookieSerialization();
        Schema::defaultStringLength(191);

        /*
        \Event::listen('Illuminate\Database\Events\QueryExecuted', function ($query) {
            echo '<pre>';
            print_r([ $query->sql, $query->time]);
            echo '</pre>';
        });

        */
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }
}
