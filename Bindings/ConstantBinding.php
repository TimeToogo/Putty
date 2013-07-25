<?php

namespace Putty\Bindings;

use \Putty\Exceptions;
use \Putty\Lifecycles;

class ConstantBinding extends ConstrainedBinding {    
    public function __construct($ParentType, $BoundToConstantValue,
            array $WhenInjectedIntoParentClasses = array(),
            array $WhenInjectedIntoExactClasses = array()) {
        
        parent::__construct($ParentType, $BoundToConstantValue, new Lifecycles\Singleton(),
                $WhenInjectedIntoParentClasses, 
                $WhenInjectedIntoExactClasses);
    }
    
    protected function SetBoundTo($BoundToConstantValue) {
        $ConstantValueType = $this->GetParentType();
        if(!($BoundToConstantValue instanceof $ConstantValueType))
            throw new Exceptions\InvalidBindingException(
                    'Constant value must be an instance of ' . $this->GetParentType());
        
        parent::SetBoundTo($BoundToConstantValue);
    }
    
    public function SetLifecycle(Lifecycles\Lifecycle $Lifecycle) {
        $BoundToConstantValue = $this->BoundTo();
        $Lifecycle->ResolveInstanceFactory(function () use(&$BoundToConstantValue) {
            return $BoundToConstantValue;
        });
        
        parent::SetLifecycle($Lifecycle);
    }
}

?>
