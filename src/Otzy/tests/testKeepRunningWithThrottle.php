<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Otzy\Intensity\IntensityThrottle;

$f = \Otzy\Forker\Forker::getInstance();

$f->addTask(function ($s) {
    sleep(rand(1, 2));
    echo $s . " finished\n";
}, ['task 1'], 1)
    ->addTask(function ($s) {
        sleep(rand(2, 3));
        echo $s . " finished\n";
    }, ['task 2'], 2);


$storage = new Memcached();
$storage->addServer('localhost', 11211);
$throttle = new IntensityThrottle('testKeepRunningWithThrottle', $storage);
$throttle->addLimit(3, 3);

$f->setThrottle($throttle);

$f->runTasks()->keepRunning();