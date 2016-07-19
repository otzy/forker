<?php

namespace Otzy\Forker;

class Forker
{

    /**
     * @var Forker
     */
    protected static $instance;

    /**
     * @return ForkerInterface
     * @throws 
     */
    public static function getInstance()
    {
        if (!(self::$instance instanceof ForkerInterface)) {
            if (is_callable('pcntl_fork')){
                self::$instance = new PcntlForker();
            }else{
                throw new \Exception('Process control extension is not installed');
            }
        }

        return self::$instance;
    }

    protected function __construct()
    {
        
    }

    
}