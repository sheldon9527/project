<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        // 默认语言为英文
        $language = \Request::get('language') ?: \Request::header('accept-language') ?: 'en';
        $language = strtolower(substr($language, 0, 2));
        //有fallback_locale 所以不用担心传入错误
        app()->setLocale($language);
    }

    /**
     * Register any application services.
     */
    public function register()
    {
    }
}
