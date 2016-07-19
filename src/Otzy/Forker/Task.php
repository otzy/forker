<?php
/**
 * Created by PhpStorm.
 * User: eugene
 * Date: 7/3/2016
 * Time: 1:18 AM
 */

namespace Otzy\Forker;


class Task
{
    /**
     * @var \Closure
     */
    private $callback;

    /**
     * @var array
     */
    private $arguments;

    /**
     * @var int
     */
    private $how_many_to_run;

    /**
     * @var int
     */
    private $how_many_running;

    /**
     * @var int|int[]
     */
    private $exit_status;

    private $name;

    public function __construct(\Closure $callback, array $arguments, $how_many_to_run=1, $name)
    {
        $this->setCallback($callback);
        $this->setArguments($arguments);
        $this->setHowManyToRun($how_many_to_run);
        $this->how_many_running = 0;
        $this->name = $name;
        if ($how_many_to_run > 1){
            $this->exit_status = [];
        }
    }

    public function getName(){
        return $this->name;
    }

    /**
     * @return \Closure
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * @param \Closure $callback
     */
    public function setCallback($callback)
    {
        $this->callback = $callback;
    }

    /**
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @param array $arguments
     */
    public function setArguments($arguments)
    {
        $this->arguments = $arguments;
    }

    /**
     * @return int
     */
    public function getHowManyToRun()
    {
        return $this->how_many_to_run;
    }

    /**
     * @param int $how_many_to_run
     */
    public function setHowManyToRun($how_many_to_run)
    {
        $this->how_many_to_run = $how_many_to_run;
    }

    /**
     * @return int
     */
    public function howManyRunning()
    {
        return $this->how_many_running;
    }
    
    public function incRunningCount(){
        $this->how_many_running++;
    }
    
    public function decRunningCount(){
        $this->how_many_running--;
    }

    /**
     * @return int|\int[]
     */
    public function getExitStatus()
    {
        return $this->exit_status;
    }

    /**
     * @param int|\int[] $exit_status
     */
    public function setExitStatus($exit_status)
    {
        if ($this->getHowManyToRun() > 1){
            $this->exit_status[] = $exit_status;
        }else{
            $this->exit_status = $exit_status;
        }
    }

}