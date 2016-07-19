<?php
declare(ticks = 1);
namespace Otzy\Forker;


class PcntlForker implements ForkerInterface
{
    /**
     * @var Task[]
     */
    protected $tasks;

    /**
     * @var Task[]
     */
    protected $running_tasks;

    /**
     * @var bool
     */
    protected static $is_parent = true;

    protected static $sig_handler_installed = false;

    /**
     * @var \Otzy\Intensity\IntensityThrottle
     */
    protected $throttle;

    /**
     * @var \Closure
     */
    protected static $logger;

    public function __construct()
    {
        if (!is_callable('pcntl_fork')) {
            throw new \Exception('PCNTL extension is either not installed or not supported on your system');
        }

        static::setLogger(function ($message) {
            echo $message . "\n";
        });
    }


    public function setLogger(\Closure $callback)
    {
        static::$logger = $callback;
    }

    public function addTask(\Closure $callback, array $arguments, $number_of_processes = 1, $name = '')
    {
        $name = trim(strval($name));
        if ($name == '') {
            $name = 'Task #' . (count($this->tasks) + 1);
        }
        $this->tasks[] = new Task($callback, $arguments, $number_of_processes, $name);
        return $this;
    }

    /**
     * @return ForkerInterface
     * @throws \Exception
     */
    public function runTasks()
    {
        if (!self::$is_parent) {
            return $this;
        }

        foreach ($this->tasks as $task) {
            for ($i = $task->howManyRunning(); $i < $task->getHowManyToRun(); $i++) {
                $pid = pcntl_fork();
                if ($pid == -1) {
                    throw new \Exception('pcntl_fork error');
                }

                if ($pid > 0) {
                    $task->incRunningCount();
                    $this->running_tasks[$pid] = $task;
                    static::log("Task {$task->getName()} started in pid=$pid");
                } else {
                    self::$is_parent = false;

                    call_user_func_array($task->getCallback(), $task->getArguments());
                    die;
                }
            }
        }

        return $this;
    }

    public static function isParent()
    {
        return self::$is_parent;
    }

    public function waitChildren()
    {
        if (!self::$is_parent) {
            return;
        }

        //wait until all children finished
        if (function_exists('pcntl_wait')) {
            $status = 0;
            while (($pid = pcntl_wait($status)) > 0) {
                $this->running_tasks[$pid]->decRunningCount();
                $exit_status = pcntl_wexitstatus($status);
                static::log($this->running_tasks[$pid]->getName() . ' exited with status ' . $exit_status);
                $this->running_tasks[$pid]->setExitStatus($exit_status);
            }
        }

    }

    /**
     * @param null|\Closure $error_handler
     * @throws \Exception
     */
    public function keepRunning($error_handler = null)
    {
        $status = 0;
        while (($pid = pcntl_wait($status)) > 0) {
            $exit_status = pcntl_wexitstatus($status);
            static::log($this->running_tasks[$pid]->getName() . ' pid=' . $pid . ' exited with status ' . $exit_status);
            $this->running_tasks[$pid]->decRunningCount();

            if (is_object($this->throttle)) {
                if (!$this->throttle->drip()) {
                    throw new ErrorRateExceededException("Task restart rate exceeded the limit.");
                }
            }

            if ($exit_status != 0 && is_callable($error_handler)) {
                call_user_func($error_handler, $exit_status);
            }

            //run necessary tasks again
            $this->runTasks();
        }
    }

    /**
     * @param \Otzy\Intensity\IntensityThrottle $throttle
     */
    public function setThrottle($throttle)
    {
        $this->throttle = $throttle;
    }

    public static function log($message)
    {
        if (!is_callable(static::$logger)) {
            return;
        }

        call_user_func(static::$logger, $message);
    }
}