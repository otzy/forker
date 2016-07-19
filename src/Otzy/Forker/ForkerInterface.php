<?php
namespace Otzy\Forker;

interface ForkerInterface
{

    /**
     * @param \Closure $callback
     * @param array $arguments
     * @param int $number_of_processes
     * @return ForkerInterface
     */
    public function addTask(\Closure $callback, array $arguments, $number_of_processes = 1);

    /**
     * @return ForkerInterface
     */
    public function runTasks();

    public function waitChildren();

    /**
     * @param null|\Closure $error_handler
     */
    public function keepRunning($error_handler = null);

    /**
     * @param string $message
     * @return void
     */
    public static function log($message);

    /**
     * @param \Otzy\Intensity\IntensityThrottle $throttle
     */
    public function setThrottle($throttle);
}