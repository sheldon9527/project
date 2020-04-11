<?php
namespace App\Console\Commands;

define('INIT_PATH', dirname(dirname(dirname(__DIR__))));
require_once INIT_PATH . '/vendor/autoload.php';
use Workerman\Worker;

// Create a Websocket server
$ws_worker = new Worker("websocket://0.0.0.0:2346");
// 4 processes
$ws_worker->count = 4;
// Emitted when new connection come
$ws_worker->onConnect = function ($connection) {
    echo "New connection\n";
};
// Emitted when data received
$ws_worker->onMessage = function ($connection, $data) {
    echo '接受客户端数据====='.$data."\n";
    // Send hello $data
    $connection->send('hello 收到');
    echo "发送到客户端的数据是=====hello 收到\n";
};
// Emitted when connection closed
$ws_worker->onClose = function ($connection) {
    echo "Connection closed\n";
};

// Run worker
Worker::runAll();
