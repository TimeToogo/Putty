<?php

namespace Putty\Exceptions;

class CircularDependencyException extends \Exception{
    public function __construct(array $DependencyTrace) {
        $Message = 'A circular dependancy has been detected. Trace: ';
        $DependencyTraceString = print_r($DependencyTrace, true);
        $Message .= $DependencyTraceString;
        
        parent::__construct($Message);
    }
}

?>
