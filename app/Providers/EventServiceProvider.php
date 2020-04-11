<?php

namespace App\Providers;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        // 跨域添加默认的返回头
        'Dingo\Api\Event\ResponseWasMorphed'            => [
            'App\Listeners\AddBaseHeaderToResponse',
        ],
    ];

    /**
     * Register any other events for your application.
     *
     * @param \Illuminate\Contracts\Events\Dispatcher $events
     */
    public function boot(DispatcherContract $events)
    {
        parent::boot($events);

        // 对资金相关的日志，统一提取到一个文件中
        // 公共的log文件中都有相关的信息，现在只是提取出来资金相关的
        \Log::listen(function ($level, $message, $context) {
            if ($level == 'info' && is_string($message) && starts_with($message, '[FINANCIAL]')) {
                // 已月为单位生成付款文件
                $filename = 'financial-' . date('m') . '.log';
                \Storage::disk('log')->append($filename, $message);
            }
        });
    }
}
