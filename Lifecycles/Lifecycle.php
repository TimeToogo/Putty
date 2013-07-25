<?php

namespace Putty\Lifecycles;

use \Putty\Exceptions;

abstract class Lifecycle {
    private $InstanceFactory = null;
    
    public function __construct() {
    }
    
    final public function ResolveInstanceFactory(callable $InstanceFactory) {
        $this->InstanceFactory = $InstanceFactory;
    }
    
    final public function IsResolved() {
        return $this->InstanceFactory !== null;
    }
    
    protected function GetInstanceFactory() {
        return $this->InstanceFactory;
    }

    public abstract function GetInstance();
}

?>
