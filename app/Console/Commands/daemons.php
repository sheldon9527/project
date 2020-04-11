<?php
namespace App\Console\Commands;

define('INIT_PATH', dirname(dirname(dirname(__DIR__))));
require_once INIT_PATH . '/vendor/autoload.php';
use Workerman\Worker;
use Workerman\Lib\Timer;

$task = new Worker();
$task->count = 2;
$task->user = 'www';
$task->group = 'www';
$task->onWorkerStart = function ($task) {
    // 1 seconds
    $time_interval = 5;
    $timer_id = Timer::add(
        $time_interval,
        function () {
            echo "Timer run\n";
        }
    );
};

// run all workers
Worker::runAll();
