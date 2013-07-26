<?php

namespace Putty\Syntax;

use \Putty\Bindings;

trait FluentBindingSyntax {
    protected $Bindings = array();
    
    public function Bind($ParentType) {
        return new FluentBindingToSyntax($this->Bindings, 
                $ParentType);
    }
}
?>
