<?php

namespace Putty\Syntax;

use \Putty\PuttyModule;

trait ModuleRegistrationSyntax {
    protected $Modules = array();
    
    public function Register(PuttyModule $Module) {
        $this->Modules[] = $Module;
    }
}

?>
