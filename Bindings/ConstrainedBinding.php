<?php

namespace Putty\Bindings;

use \Putty\Exceptions;

abstract class ConstrainedBinding extends Binding {
    private $StoredWhenInjectedIntoParentClasses = array();
    private $StoredWhenInjectedIntoExactClasses = array();
    
    private $WhenInjectedIntoParentClasses = array();
    private $WhenInjectedIntoExactClasses = array();
    
    public function __construct($ParentType, $BoundTo, $LazyLoad = false,
            array $WhenInjectedIntoParentClasses = array(),
            array $WhenInjectedIntoExactClasses = array()) {
        
        $this->StoredWhenInjectedIntoParentClasses = $WhenInjectedIntoParentClasses;
        $this->StoredWhenInjectedIntoExactClasses = $WhenInjectedIntoExactClasses;
        
        parent::__construct($ParentType, $BoundTo, $LazyLoad);
    }
    
    protected function Initialize() {
        parent::Initialize();
        
        $this->SetWhenInjectedInto($this->WhenInjectedIntoParentClasses);
        $this->SetWhenExactlyInjectedInto($this->WhenInjectedIntoExactClasses);
    }
    
    public function WhenInjectedIntoParentClasses() {
        if(!$this->IsInitialized())
            return $this->StoredWhenInjectedIntoParentClasses;
        return $this->WhenInjectedIntoParentClasses;
    }
    public function SetWhenInjectedInto(array $ParentClasses) {
        if(!$this->IsInitialized())
            return $this->StoredWhenInjectedIntoParentClasses = $ParentClasses;
        $this->WhenInjectedIntoParentClasses = array();
        foreach ($ParentClasses as $ParentClass) {
            $this->AddWhenInjectedInto($ParentClass);
        }
    }
    public function AddWhenInjectedInto($ParentClass) {
        if(!$this->IsInitialized())
            return $this->StoredWhenInjectedIntoParentClasses[] = $ParentClasses;
        $this->VerifyValidType($ParentClass);
        
        $this->WhenInjectedIntoParentClasses[] = $ParentClass;
    }
    
    public function WhenInjectedIntoExactClasses() {
        if(!$this->IsInitialized())
            return $this->StoredWhenInjectedIntoExactClasses;
        
        return $this->WhenInjectedIntoExactClasses;
    }
    public function SetWhenExactlyInjectedInto(array $ExactClasses) {
        if(!$this->IsInitialized())
            return $this->StoredWhenInjectedIntoExactClasses[] = $ExactClasses;
        foreach ($ExactClasses as $ExactClass) {
            $this->AddWhenInjectedExactlyInto($ExactClass);
        }
    }
    public function AddWhenInjectedExactlyInto($ExactClass) {
        if(!$this->IsInitialized())
            return $this->StoredWhenInjectedIntoExactClasses[] = $ExactClass;
        
        $this->VerifyValidType($ExactClass);
        $Type = new \ReflectionClass($ExactClass);
        if(!$Type->isInstantiable())
            throw new Exceptions\InvalidBindingException($ExactClass . ' must be instantiable');
        $this->WhenInjectedIntoParentClasses[] = $ExactClass;
    }
    
    public function IsConstrained() {
        if(!$this->IsInitialized())
            return (count($this->StoredWhenInjectedIntoParentClasses) !== 0
                || count($this->StoredWhenInjectedIntoExactClasses) !== 0);
        return (count($this->WhenInjectedIntoParentClasses) !== 0
                || count($this->WhenInjectedIntoExactClasses) !== 0);
    }
    public function ExactlyMatches($Class) {
        if(!$this->IsConstrained())
            return true;
        
        foreach ($this->WhenInjectedIntoExactClasses() as $ExactClass) {
            if($ExactClass === $Class)
                return true;
        }

        return false;
    }
    public function Matches($Class) {
        $this->VerifyInitialized();
        if(!$this->IsConstrained())
            return true;
        
        $Reflection = new \ReflectionClass($Class);
        foreach ($this->WhenInjectedIntoParentClasses() as $ParentClass) {
            if($Reflection->isSubclassOf($ParentClass) || 
                    $Class === $ParentClass)
                return true;
        }

        return false;
    }
    
    private function VerifyValidType($Type) {
        if(!class_exists($Type) && !interface_exists($Type))
            throw new \InvalidArgumentException($Type . ' is not valid type');
    }
}

?>
