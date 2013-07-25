<?php

namespace Putty\Bindings;

use \Putty\Exceptions;
use \Putty\Lifecycles;

abstract class Binding {
    private $ParentType;
    private $BoundTo;
    private $Lifecycle;
    
    public function __construct($ParentType, $BoundTo, Lifecycles\Lifecycle $Lifecycle) {
        try
        {
            $this->SetParentType($ParentType);
            $this->SetBoundTo($BoundTo);
            $this->SetLifecycle($Lifecycle);
        }
        catch (ReflectionException $Exception) {
           throw new Exceptions\InvalidBindingException(null, $Exception);
        }
    }
    
    public function GetParentType() {
        return $this->ParentType;
    }
    protected function SetParentType($ParentType) {
        $EnsureValidType = new \ReflectionClass($ParentType);
        
        $this->ParentType = $ParentType;
    }
    
    public function BoundTo() {
        return $this->BoundTo;
    }
    protected function SetBoundTo($BoundTo) {
        $this->BoundTo = $BoundTo;
    }
    
    public function GetLifecycle() {
        return $this->Lifecycle;
    }
    public function SetLifecycle(Lifecycles\Lifecycle $Lifecycle) {
        $this->Lifecycle = $Lifecycle;
    }
}

?>
