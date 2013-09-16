<?php

namespace Putty\Syntax;

trait FluentBindingSyntax {
    private $Bindings = array();
    protected $LazyLoad = false;
    protected $DefaultLifecycle = null;
    
    
    public function Bind($ParentType) {
        return new FluentBindingToSyntax($this->Bindings, $ParentType, $this->LazyLoad);
    }
}
?>
