<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        'Symfony\Component\HttpKernel\Exception\HttpException',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param \Exception $e
     */
    public function report(Exception $e)
    {
        if (env('ERROR_NOTIFY')) {
            $data = [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'error_message' => $e,
            ];

            $key = 'error_notify_'.md5($e->getMessage());

            if (!\Cache::has($key)) {
                \Cache::put($key, 1, 10);

                \Mail::send('email.system-error', $data, function ($message) {
                    $notifyUsers = config('error.email_notify_users');
                    foreach ($notifyUsers as $user) {
                        $message->to($user)->subject('服务器报错通知');
                    }
                });
            }
        }

        return parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception               $e
     *
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        return parent::render($request, $e);
    }
}
