<?php

namespace Putty\Syntax;

use \Putty\Bindings;

abstract class FluentConstrainedBindingSyntax {
    private $Binding;
    
    public function __construct(Bindings\ConstrainedBinding &$Binding) {
        $this->Binding = &$Binding;
    }
    
    public function WhenInjectedInto($ParentType) {
        $this->Binding->AddWhenInjectedInto($ParentType);
        
        return $this;
    }
    
    public function WhenInjectedExactlyInto($Class) {
        $this->Binding->AddWhenInjectedExactlyInto($Class);
        
        return $this;
    }
}

?>
