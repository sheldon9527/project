<?php

namespace App\Listeners;

use Dingo\Api\Event\ResponseWasMorphed;

class AddBaseHeaderToResponse
{
    public function handle(ResponseWasMorphed $event)
    {
        // 增加跨域需要使用的头
        $headers = $event->response->headers;
        $headers->set('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization');
        $headers->set('Access-Control-Max-Age', 60);
        $headers->set('Access-Control-Allow-Origin', '*');
        $headers->set('Access-Control-Allow-Methods', 'OPTIONS, GET, HEAD, POST, PUT, DELETE, TRACE, CONNECT');
    }
}
