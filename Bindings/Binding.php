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
    
    public abstract function RequiresResolution();
    
    public function GetResolutionRequirements() {
        if(!$this->RequiresResolution())
            throw new \LogicException('This binding does not require resolution');
        
        return $this->GenerateResolutionRequirements();
    }
    protected abstract function GenerateResolutionRequirements();
    
    public function ResolveRequirements(BindingResolutionRequirements $ResolvedRequirements) {
        if(!$this->RequiresResolution())
            throw new \LogicException('This binding does not require resolution');
        
        $this->ResolveResolutionRequirements($ResolvedRequirements);
    }
    protected abstract function ResolveResolutionRequirements
            (BindingResolutionRequirements $ResolvedRequirements);
    
    public function GetInstance() {
        if($this->RequiresResolution())
            throw new \LogicException
                    ('Binding cannot generate instance: Requirements are not resolved');
        
        return $this->GenerateInstance();
    }
    protected abstract function GenerateInstance();
}

?>
