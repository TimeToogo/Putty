<?php

namespace Putty\Bindings;

use \Putty\Exceptions;

class ConstantBinding extends ConstrainedBinding {    
    public function __construct($ParentType, $BoundToConstantValue,
            array $WhenInjectedIntoParentClasses = array(),
            array $WhenInjectedIntoExactClasses = array()) {
        
        parent::__construct($ParentType, $BoundToConstantValue,
                $WhenInjectedIntoParentClasses, $WhenInjectedIntoExactClasses);
    }
    
    protected function SetBoundTo($BoundToConstantValue) {
        $ConstantValueType = $this->GetParentType();
        if(!($BoundToConstantValue instanceof $ConstantValueType))
            throw new Exceptions\InvalidBindingException(
                    'Constant value must be an instance of ' . $this->GetParentType());
        
        parent::SetBoundTo($BoundToConstantValue);
    }

    public function RequiresResolution() {
        return false;
    }

    protected function ResolveResolutionRequirements
            (BindingResolutionRequirements $ResolvedRequirements) {
        
    }
    
    protected function GenerateResolutionRequirements() {
        return null;
    }
    
    protected function GenerateInstance() {
        return $this->BoundTo();
    }
}

?>
