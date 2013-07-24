<?php

namespace Putty\Syntax;

use \Putty\PuttyModule;

abstract class ModuleRegistrationSyntax {
    protected $Modules = array();
    
    public function Register(PuttyModule $Module) {
        $this->Modules[] = $Module;
    }
}

?>
