<?php

namespace Putty\Bindings;

use \Putty\Lifecycles;

class SelfBinding extends ClassBinding {
    public function __construct($BindingType, 
            array $ConstantConstructorArgs = array(),
            Lifecycles\Lifecycle $Lifecycle = null,
            array $WhenInjectedIntoParentClasses = array(),
            array $WhenInjectedIntoExactClasses = array()) {
        
        parent::__construct($BindingType, $BindingType, 
                $ConstantConstructorArgs, 
                $Lifecycle, 
                $WhenInjectedIntoParentClasses, 
                $WhenInjectedIntoExactClasses);
    }
}

?>
