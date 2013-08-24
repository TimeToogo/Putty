<?php

namespace Putty;

use \Putty\Syntax;

abstract class PuttyModule {
    use Syntax\FluentBindingSyntax;
    
    public function __construct() { }
    
    protected abstract function InitializeBindings();
    
    public function GetBindings() {
        $this->InitializeBindings();
        return $this->Bindings;
    }
}

?>
