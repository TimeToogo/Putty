<?php

namespace Putty\Bindings;

class BindingResolutionRequirements {
    private $RequiredTypes = array();
    private $RequiredTypeNames = array();
    private $ResolvedTypesDictionary = array();
    
    public function AddRequiredType(\ReflectionClass $Type) {
        if($this->IsRequired($Type))
            throw new \LogicException('Supplied type already required');
        
        $this->RequiredTypes[] = $Type;
        $this->RequiredTypeNames[] = $Type->getName();
    }
    
    public function GetRequiredTypes() {
        return $this->RequiredTypes;
    }
    
    public function ResolveRequiredType(\ReflectionClass $Type, \Closure $TypeFactory) {
        if(!$this->IsRequired($Type))
            throw new \LogicException('Supplied type not required');
        
        $this->ResolvedTypesDictionary[$Type->getName()] = $TypeFactory;
    }
    
    public function AreRequirementsResolved() {
        foreach($this->RequiredTypeNames as $TypeName) {
            if(!array_key_exists($TypeName, $this->ResolvedTypesDictionary))
                return false;
        }
        return true;
    }
    
    public function GetTypeResolution(\ReflectionClass $Type) {
        if(!$this->IsRequired($Type))
            throw new \LogicException('Supplied type not required');
        
        return $this->ResolvedTypesDictionary[$Type->getName()];
    }
    
    private function IsRequired(\ReflectionClass $Type) {
        return in_array($Type->getName(), $this->RequiredTypeNames);
    }
}

?>
