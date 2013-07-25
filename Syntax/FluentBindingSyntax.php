<?php

namespace Putty\Syntax;

use \Putty\Bindings;

abstract class FluentBindingSyntax {
    protected $Bindings = array();
    
    public function Bind($ParentType) {
        return new FluentBindingToSyntax($this->Bindings, 
                $ParentType);
    }
}
?>
