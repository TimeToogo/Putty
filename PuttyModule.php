<?php

namespace Putty;

use \Putty\Syntax;

abstract class PuttyModule extends Syntax\FluentBindingSyntax {
    final public function __construct() {
        $this->InitializeBindings();
    }
    
    protected abstract function InitializeBindings();
    
    public function GetClassBindings() {
        return $this->ClassBindings;
    }
    
    public function GetConstantBindings() {
        return $this->ConstantBindings;
    }
}

?>
