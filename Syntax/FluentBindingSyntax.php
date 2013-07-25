<?php

namespace Putty\Syntax;

use \Putty\Bindings;

abstract class FluentBindingSyntax {
    protected $ClassBindings = array();
    protected $ConstantBindings = array();
    
    public function Bind($ParentType) {
        return new FluentBindingToSyntax($this->ClassBindings, $this->ConstantBindings, 
                $ParentType);
    }
}
?>
