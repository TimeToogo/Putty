<?php

namespace Putty;

use \Putty\Exceptions;

class Binding {
    private $ParentClassOrInterface;
    private $BoundTo;
    private $WhenInjectedIntoParentClasses = array();
    private $WhenInjectedIntoExactClasses = array();
    private $ConstantConstructorArgs = array();
    private $IsSingleton = false;
    private $SingletonInstance = null;
    
    public function __construct($ParentClassOrInterface, $BoundTo, 
            array $ConstantConstructorArgs = array(), 
            array $WhenInjectedIntoParentClasses = array(),
            array $WhenInjectedIntoExactClasses = array(),
            $IsSingleton = false) {
        try
        {
            $this->ParentClassOrInterface = new \ReflectionClass($ParentClassOrInterface);
            
            $this->BoundTo = new \ReflectionClass($BoundTo);
            
            if(!$this->BoundTo->isInstantiable())
                throw new Exceptions\InvalidBindingException(
                        $BoundTo . ' must be instantiable');
            
            if(!$this->BoundTo->isSubclassOf($ParentClassOrInterface) 
                    || $ParentClassOrInterface === $BoundTo)
                throw new Exceptions\InvalidBindingException(
                        $BoundTo . ' is not a valid subclass of' . $ParentClassOrInterface);
                
            $this->SetWhenInjectedInto($WhenInjectedIntoParentClasses);
            
            $this->SetWhenExactlyInjectedInto($WhenInjectedIntoExactClasses);
            
            $this->SetConstantConstructorArgs($ConstantConstructorArgs);
            
            $this->SetIsSingleton($IsSingleton);
        }
        catch (ReflectionException $Exception) {
           throw new Exceptions\InvalidBindingException(null, $Exception);
        }
    }
    
    public function GetParentClassOrInterface() {
        return $this->ParentClassOrInterface;
    }
    
    public function BoundTo() {
        return $this->BoundTo;
    }
    
    public function WhenInjectedIntoParentClasses() {
        return $this->WhenInjectedIntoParentClasses();
    }
    public function SetWhenInjectedInto(array $ParentClasses) {
        $this->WhenInjectedIntoParentClasses = array();
        foreach ($ParentClasses as $ParentClass) {
            $this->AddWhenInjectedInto($ParentClass);
        }
    }
    public function AddWhenInjectedInto($ParentClass) {
        $this->WhenInjectedIntoParentClasses[] = new \ReflectionClass($ParentClass);
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
        $Reflection = new \ReflectionClass($ExactClass);
        if(!$Reflection->isInstantiable())
            throw new Exceptions\InvalidBindingException($ExactClass . ' must be instantiable');
        $this->WhenInjectedIntoParentClasses[] = $Reflection;
    }


    public function GetConstantConstructorArgs() {
        return $this->ConstantConstructorArgs;
    }
    public function SetConstantConstructorArgs(array $ConstantConstructorArgs) {
        $this->ConstantConstructorArgs = $ConstantConstructorArgs;
    }
    public function AddConstantConstructorArgs($ParameterName, $Value) {
        $this->ConstantConstructorArgs[$ParameterName] = $Value;
    }
    
    public function IsSingleton(){
        return $this->IsSingleton;
    }
    public function SetIsSingleton($IsSingleton) {
        $this->IsSingleton = $IsSingleton;
    }
    public function ResolveSingleton($Instance){
        if(!$this->IsSingleton)
            throw new \LogicException('This binding is not a singleton binding');
        $this->SingletonInstance = $Instance;
    }
    public function IsSingletonResolved(){
        return $this->SingletonInstance !== null;
    }
    public function GetSingletonInstance() {
        if(!$this->IsSingleton)
            throw new \LogicException('This binding is not a singleton binding');
        return $this->SingletonInstance;
    }
    
    public function IsConstrained() {
        return (count($this->WhenInjectedIntoParentClasses) !== 0
                || count($this->WhenInjectedIntoExactClasses) !== 0);
    }
    public function ExactlyMatches($Class) {
        if(!$this->IsConstrained())
            return true;
        
        foreach ($this->WhenInjectedIntoExactClasses as $ExactClass) {
            if($ExactClass->getName() === $Class)
                return true;
        }

        return false;
    }
    public function Matches($Class) {
        if(!$this->IsConstrained())
            return true;
        
        $Reflection = new \ReflectionClass($Class);
        foreach ($this->WhenInjectedIntoParentClasses as $ParentClass) {
            if($Reflection->isSubclassOf($ParentClass->getName()) || 
                    $Class === $ParentClass->getName())
                return true;
        }

        return false;
    }
}

?>
