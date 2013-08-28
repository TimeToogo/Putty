<?php

namespace Putty;

use \Putty\Syntax;

abstract class PuttyModule {
    use Syntax\FluentBindingSyntax;
    
    private $LazyLoad;
    
    public function __construct() { }
    
    protected abstract function InitializeBindings();
    
    public function GetBindings($LazyLoad) {
        $this->LazyLoad = $LazyLoad;
        $this->InitializeBindings();
        return $this->Bindings;
    }
}

?>
