<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middleware = [
        'Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode',
        //'Illuminate\Cookie\Middleware\EncryptCookies',
        'App\Http\Middleware\EncryptCookies',
        'Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse',
        'Illuminate\Session\Middleware\StartSession',
        'Illuminate\View\Middleware\ShareErrorsFromSession',
        'LucaDegasperi\OAuth2Server\Middleware\OAuthExceptionHandlerMiddleware',
        //'App\Http\Middleware\VerifyCsrfToken',
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        //'auth' => 'App\Http\Middleware\Authenticate',
        //'auth.basic' => 'Illuminate\Auth\Middleware\AuthenticateWithBasicAuth',
        //'guest' => 'App\Http\Middleware\RedirectIfAuthenticated',
        'admin.auth' => \App\Http\Middleware\AdminAuthenticate::class,
        'oauth' => \LucaDegasperi\OAuth2Server\Middleware\OAuthMiddleware::class,
        'oauth-user' => \LucaDegasperi\OAuth2Server\Middleware\OAuthUserOwnerMiddleware::class,
        'oauth-client' => \LucaDegasperi\OAuth2Server\Middleware\OAuthClientOwnerMiddleware::class,
        'check-authorization-params' => \LucaDegasperi\OAuth2Server\Middleware\CheckAuthCodeRequestMiddleware::class,
        'csrf' => \App\Http\Middleware\VerifyCsrfToken::class
    ];
}
