<?php

namespace Putty\Syntax;

trait FluentBindingSyntax {
    private $Bindings = array();
    private $LazyLoad = false;
    
    public function Bind($ParentType) {
        return new FluentBindingToSyntax($this->Bindings, $ParentType, $this->LazyLoad);
    }
}
?>
