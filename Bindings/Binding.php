<?php

namespace Putty\Bindings;

use \Putty\Exceptions;
use \Putty\Lifecycles;

abstract class Binding {
    private $ParentType;
    private $BoundTo;
    
    public function __construct($ParentType, $BoundTo) {
        try
        {
            $this->SetParentType($ParentType);
            $this->SetBoundTo($BoundTo);
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
}

?>
