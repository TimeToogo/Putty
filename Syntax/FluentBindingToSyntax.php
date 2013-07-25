<?php

namespace Putty\Syntax;

use \Putty\Bindings;

class FluentBindingToSyntax {
    private $ClassBindings = array();
    private $ConstantBindings = array();
    private $ParentType = null;

    public function __construct(&$ClassBindings, &$ConstantBindings, $ParentType) {
        $this->ClassBindings = &$ClassBindings;
        $this->ConstantBindings = &$ConstantBindings;
        $this->ParentType = $ParentType;
    }
    
    public function To($Class) {
        $Binding = new Bindings\ClassBinding($this->ParentType, $Class);
        $this->ClassBindings[] = $Binding;
        
        return new FluentClassBindingSettingsSyntax($Binding);
    }
    
    public function ToConstant($Value) {
        $Binding = new Bindings\ConstantBinding($this->ParentType, $Value);
        $this->ConstantBindings[] = $Binding;
        
        return new FluentConstantBindingSettingsSyntax($Binding);
    }
}

?>
