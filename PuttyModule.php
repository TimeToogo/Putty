<?php

namespace Putty;

use \Putty\Syntax;

abstract class PuttyModule extends Syntax\FluentBindingsSyntax {
    final public function __construct() { }
    
    protected abstract function InitializeBindings();
    
    public function GetBindings() {
        $this->InitializeBindings();
        return $this->Bindings;
    }
}

?>
