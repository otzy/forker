<?php
require_once __DIR__ . '/../../vendor/autoload.php';

$f = \Otzy\Forker\Forker::getInstance();

$f->addTask(function ($s) {
                    sleep(rand(1, 3));
                    echo $s . " finished\n";
                }, ['task 1'], 1)
   ->addTask(function ($s) {
                    sleep(rand(2, 5));
                    echo $s . " finished\n";
                }, ['task 2'], 2);

$f->runTasks()->keepRunning();