<?php

namespace Putty\Syntax;

use \Putty\Bindings;

class FluentBindingToSyntax {
    private $Bindings = array();
    private $ParentType = null;
    private $LazyLoad;

    public function __construct(&$Bindings, $ParentType, $LazyLoad = false) {
        $this->Bindings = &$Bindings;
        $this->ParentType = $ParentType;
        $this->LazyLoad = $LazyLoad;
    }
    
    public function To($Class) {
        $Binding = new Bindings\ClassBinding($this->ParentType, $Class, $this->LazyLoad);
        $this->Bindings[] = $Binding;
        
        return new FluentClassBindingSettingsSyntax($Binding);
    }
    
    public function ToSelf() {
        $Binding = new Bindings\SelfBinding($this->ParentType, $this->LazyLoad);
        $this->Bindings[] = $Binding;
        
        return new FluentClassBindingSettingsSyntax($Binding);
    }
    
    public function ToConstant($Value) {
        $Binding = new Bindings\ConstantBinding($this->ParentType, $Value, $this->LazyLoad);
        $this->Bindings[] = $Binding;
        
        return new FluentConstantBindingSettingsSyntax($Binding);
    }
}

?>
