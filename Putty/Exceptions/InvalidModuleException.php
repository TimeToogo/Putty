<?php

namespace Putty\Exceptions;

class InvalidModuleException extends \Exception{
    public function __construct($Message = 'The supplied module is invalid') {
        parent::__construct($Message);
    }
}

?>
