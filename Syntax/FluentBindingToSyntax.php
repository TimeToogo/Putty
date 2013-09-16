<?php

namespace Putty\Syntax;

use \Putty\Bindings;
use \Putty\Lifecycles\Lifecycle;

class FluentBindingToSyntax {
    private $Bindings = array();
    private $ParentType = null;
    private $LazyLoad;
    private $DefaultLifecycle;

    public function __construct(&$Bindings, $ParentType, 
            $LazyLoad = false, Lifecycle $DefaultLifecycle = null) {
        $this->Bindings = &$Bindings;
        $this->ParentType = $ParentType;
        $this->LazyLoad = $LazyLoad;
        $this->DefaultLifecycle = $DefaultLifecycle;
    }
    
    public function To($Class) {
        $Binding = new Bindings\ClassBinding($this->ParentType, $Class, $this->LazyLoad,
                array(), $this->GetDefaultLifecycle());
        $this->Bindings[] = $Binding;
        
        return new FluentClassBindingSettingsSyntax($Binding);
    }
    
    public function ToSelf() {
        $Binding = new Bindings\SelfBinding($this->ParentType, $this->LazyLoad,
                array(), $this->GetDefaultLifecycle());
        $this->Bindings[] = $Binding;
        
        return new FluentClassBindingSettingsSyntax($Binding);
    }
    
    public function ToConstant($Value) {
        $Binding = new Bindings\ConstantBinding($this->ParentType, $Value, $this->LazyLoad);
        $this->Bindings[] = $Binding;
        
        return new FluentConstantBindingSettingsSyntax($Binding);
    }
    
    private function GetDefaultLifecycle() {
        if($this->DefaultLifecycle === null)
            return null;
        
        $Reflection = new \ReflectionClass($this->DefaultLifecycle);
        return $Reflection->newInstanceArgs();
    }
}

?>
