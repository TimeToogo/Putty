<?php

namespace Putty\Exceptions;

class InvalidBindingException extends \Exception{
    public function __construct($InvalidMessage = null, $InnerException = null) {
        $FullMessage = 'The specified binding parameters are invalid';
        if($InvalidMessage != null)
            $FullMessage .= ': ' . $InvalidMessage;
        parent::__construct($FullMessage, 0, $InnerException);
    }
}

?>
