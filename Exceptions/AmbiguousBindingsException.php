<?php

namespace Putty\Exceptions;

class AmbiguousBindingsException extends \Exception{
    public function __construct($ExtraInfo = null) {
        $FullMessage = 'The provided bindings cannot be resolved';
        if($ExtraInfo !== null)
            $FullMessage .= ' (' . $ExtraInfo . ')';
        parent::__construct($FullMessage);
    }
}

?>
