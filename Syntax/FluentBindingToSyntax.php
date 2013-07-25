<?php

namespace Putty\Syntax;

use \Putty\Bindings;

class FluentBindingToSyntax {
    private $Bindings = array();
    private $ParentType = null;

    public function __construct(&$Bindings, $ParentType) {
        $this->Bindings = &$Bindings;
        $this->ParentType = $ParentType;
    }
    
    public function To($Class) {
        $Binding = new Bindings\ClassBinding($this->ParentType, $Class);
        $this->Bindings[] = $Binding;
        
        return new FluentClassBindingSettingsSyntax($Binding);
    }
    
    public function ToConstant($Value) {
        $Binding = new Bindings\ConstantBinding($this->ParentType, $Value);
        $this->Bindings[] = $Binding;
        
        return new FluentConstantBindingSettingsSyntax($Binding);
    }
}

?>
