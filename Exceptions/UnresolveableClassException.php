<?php

namespace Putty\Exceptions;

class UnresolveableClassException extends \Exception{
    public function __construct($Class, $ExtraInfo = null, $InnerException = null) {
        $Message = 'The supplied class cannot be resolved: ' . $Class;
        if($ExtraInfo !== null)
            $Message .= ' (' . $ExtraInfo . ')';
        parent::__construct($Message, 0, $InnerException);
    }
}

?>
