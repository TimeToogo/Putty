<?php

namespace Putty;

use \Putty\Syntax;

abstract class PuttyModule {
    use Syntax\FluentBindingSyntax;
    
    final public function __construct() {
        $this->InitializeBindings();
    }
    
    protected abstract function InitializeBindings();
    
    public function GetBindings() {
        return $this->Bindings;
    }
}

?>
