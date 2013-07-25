<?php

namespace Putty\Syntax;

use \Putty\Bindings;

class FluentClassBindingSettingsSyntax extends FluentConstrainedBindingSyntax {
    private $Binding;
    
    public function __construct(Bindings\ClassBinding &$Binding) {
        parent::__construct($Binding);
        $this->Binding = &$Binding;
    }
    
    public function WithConstructorArgument($ParameterName, $Value) {
        $this->Binding->AddConstantConstructorArgs($ParameterName, $Value);
        
        return $this;
    }
    
    public function InLifecycle(\Putty\Lifecycles\Lifecycle $Lifecycle) {
        $this->Binding->SetLifecycle($Lifecycle);
        
        return $this;
    }
    
    public function InTransientLifecycle() {
        $this->Binding->SetLifecycle(new \Putty\Lifecycles\Transient());
        
        return $this;
    }
    
    public function InSingletonLifecycle() {
        $this->Binding->SetLifecycle(new \Putty\Lifecycles\Singleton());
        
        return $this;
    }
}

class FluentConstantBindingSettingsSyntax extends FluentConstrainedBindingSyntax {
    public function __construct(Bindings\ConstantBinding &$Binding) {
        parent::__construct($Binding);
    }
}

?>
