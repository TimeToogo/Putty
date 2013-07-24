<?php

namespace Putty\Syntax;

use \Putty\Binding;

abstract class FluentBindingsSyntax {
    protected $Bindings = array();
    
    public function Bind($ParentClassOrInterface) {
        return new FluentBindingToSyntax($this->Bindings, $ParentClassOrInterface);
    }
}

class FluentBindingToSyntax {
    private $Bindings = array();
    private $ParentClassOrInterface = null;

    public function __construct(&$Bindings, $ParentClassOrInterface) {
        $this->Bindings = &$Bindings;
        $this->ParentClassOrInterface = $ParentClassOrInterface;
    }
    
    public function To($Class) {
        $Binding = new Binding($this->ParentClassOrInterface, $Class);
        $this->Bindings[] = $Binding;
        
        return new FluentBindingSettingsSyntax($Binding);
    }
}

class FluentBindingSettingsSyntax {
    private $Binding;
    
    public function __construct(Binding &$Binding) {
        $this->Binding = &$Binding;
    }
    
    public function AsSingleton() {
        $this->Binding->SetIsSingleton(true);
        
        return $this;
    }
    
    public function WithConstructorArgument($ParameterName, $Value) {
        $this->Binding->AddConstantConstructorArgs($ParameterName, $Value);
        
        return $this;
    }
    
    public function WhenInjectedInto($ParentClassOrInterface) {
        $this->Binding->AddWhenInjectedInto($ParentClassOrInterface);
        
        return $this;
    }
    
    public function WhenInjectedExactlyInto($Class) {
        $this->Binding->AddWhenInjectedExactlyInto($Class);
        
        return $this;
    }
}

?>
