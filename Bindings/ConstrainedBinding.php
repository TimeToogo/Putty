<?php

namespace Putty\Bindings;

use \Putty\Exceptions;

abstract class ConstrainedBinding extends Binding {
    private $WhenInjectedIntoParentClasses = array();
    private $WhenInjectedIntoExactClasses = array();
    
    public function __construct($ParentType, $BoundTo,
            array $WhenInjectedIntoParentClasses = array(),
            array $WhenInjectedIntoExactClasses = array()) {
        
        parent::__construct($ParentType, $BoundTo);
        $this->SetWhenInjectedInto($WhenInjectedIntoParentClasses);
        $this->SetWhenExactlyInjectedInto($WhenInjectedIntoExactClasses);
    }
    
    public function WhenInjectedIntoParentClasses() {
        return $this->WhenInjectedIntoParentClasses;
    }
    public function SetWhenInjectedInto(array $ParentClasses) {
        $this->WhenInjectedIntoParentClasses = array();
        foreach ($ParentClasses as $ParentClass) {
            $this->AddWhenInjectedInto($ParentClass);
        }
    }
    public function AddWhenInjectedInto($ParentClass) {
        $EnsureValidType = new \ReflectionClass($ParentClass);
        
        $this->WhenInjectedIntoParentClasses[] = $ParentClass;
    }
    
    public function WhenInjectedIntoExactClasses() {
        return $this->WhenInjectedIntoExactClasses;
    }
    public function SetWhenExactlyInjectedInto(array $ExactClasses) {
        foreach ($ExactClasses as $ExactClass) {
            $this->AddWhenInjectedExactlyInto($ExactClass);
        }
    }
    public function AddWhenInjectedExactlyInto($ExactClass) {
        $EnsureValidType = new \ReflectionClass($ExactClass);
        if(!$EnsureValidType->isInstantiable())
            throw new Exceptions\InvalidBindingException($ExactClass . ' must be instantiable');
        $this->WhenInjectedIntoParentClasses[] = $ExactClass;
    }
    
    public function IsConstrained() {
        return (count($this->WhenInjectedIntoParentClasses) !== 0
                || count($this->WhenInjectedIntoExactClasses) !== 0);
    }
    public function ExactlyMatches($Class) {
        if(!$this->IsConstrained())
            return true;
        
        foreach ($this->WhenInjectedIntoExactClasses as $ExactClass) {
            if($ExactClass === $Class)
                return true;
        }

        return false;
    }
    public function Matches($Class) {
        if(!$this->IsConstrained())
            return true;
        
        $Reflection = new \ReflectionClass($Class);
        foreach ($this->WhenInjectedIntoParentClasses as $ParentClass) {
            if($Reflection->isSubclassOf($ParentClass) || 
                    $Class === $ParentClass)
                return true;
        }

        return false;
    }
}

?>
