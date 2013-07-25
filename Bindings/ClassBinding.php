<?php

namespace Putty\Bindings;

use \Putty\Exceptions;
use \Putty\Lifecycles;

class ClassBinding extends ConstrainedBinding {
    private $ConstantConstructorArgs = array();
    
    public function __construct($ParentType, $BoundTo, 
            array $ConstantConstructorArgs = array(),
            Lifecycles\Lifecycle $Lifecycle = null,
            array $WhenInjectedIntoParentClasses = array(),
            array $WhenInjectedIntoExactClasses = array()) {
        try
        {
            if($Lifecycle === null)
                $Lifecycle = new Lifecycles\Transient();
            
            parent::__construct($ParentType, $BoundTo, $Lifecycle,
                    $WhenInjectedIntoParentClasses, 
                    $WhenInjectedIntoExactClasses);
            
            $this->SetConstantConstructorArgs($ConstantConstructorArgs);
        }
        catch (ReflectionException $Exception) {
           throw new Exceptions\InvalidBindingException(null, $Exception);
        }
    }
    
    protected function SetBoundTo($BoundTo) {
        $BoundToReflection = new \ReflectionClass($BoundTo);
        if(!$BoundToReflection->isInstantiable())
            throw new Exceptions\InvalidBindingException(
                    $BoundTo . ' must be instantiable');

        if(!$BoundToReflection->isSubclassOf($this->GetParentType()) 
                && $this->GetParentType() !== $BoundTo)
            throw new Exceptions\InvalidBindingException(
                    $BoundTo . ' is not a valid subclass of' . $this->GetParentType());
        
        parent::SetBoundTo($BoundTo);
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
}

?>
