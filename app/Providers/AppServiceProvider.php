<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        require_once app_path(). '/Helpers/Ciphertext.php';
        require_once app_path(). '/Helpers/Dbase.php';
        require_once app_path(). '/Helpers/Init.php';
        require_once app_path(). '/Helpers/Status.php';
        require_once app_path(). '/Helpers/Endpoint.php';
    }
}
