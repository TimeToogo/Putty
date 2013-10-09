<?php

namespace Putty\Bindings;

use \Putty\Exceptions;
use \Putty\Lifecycles;

abstract class Binding {
    private $Initialized = false;
    
    private $StoredParentType;
    private $StoredBoundTo;
    
    private $ParentType;
    private $BoundTo;
    
    public function __construct($ParentType, $BoundTo, $LazyLoad = false) {
        $this->StoredParentType = $ParentType;
        $this->StoredBoundTo = $BoundTo;
        if(!$LazyLoad)
            $this->InitializeBinding();
    }
    
    final private function InitializeBinding() {
        try
        {
            $this->Initialized = true;
            $this->SetParentType($this->StoredParentType);
            $this->SetBoundTo($this->StoredBoundTo);
            $this->Initialize();
        }
        catch (ReflectionException $Exception) {
           $this->Initialized = false;
           throw new Exceptions\InvalidBindingException(null, $Exception);
        }
    }
    protected function Initialize() { }
    final protected function IsInitialized() {
        return $this->Initialized;
    }
    final protected function VerifyInitialized() {
        if(!$this->Initialized)
            $this->InitializeBinding();
    }

    public function GetParentType() {
        if(!$this->IsInitialized())
            return $this->StoredParentType;
        return $this->ParentType;
    }
    protected function SetParentType($ParentType) {
        if(!$this->IsInitialized())
            return $this->StoredParentType = $ParentType;
        $EnsureValidType = new \ReflectionClass($ParentType);
        
        $this->ParentType = $ParentType;
    }
    
    public function BoundTo() {
        if(!$this->IsInitialized())
            return $this->StoredBoundTo;
        
        return $this->BoundTo;
    }
    final public function BoundToClass() {
        $BoundTo = $this->BoundTo();
        if(!is_string($BoundTo))
            return get_class($BoundTo);
        else
            return $BoundTo;
    }
    protected function SetBoundTo($BoundTo) {
        if(!$this->IsInitialized())
            return $this->StoredBoundTo = $BoundTo;
        
        $this->BoundTo = $BoundTo;
    }
    
    public function RequiresResolution() {
        $this->VerifyInitialized();
        return $this->BindingRequiresResolution();
    }
    public abstract function BindingRequiresResolution();
    
    final public function GetResolutionRequirements() {
        if(!$this->RequiresResolution())
            throw new \LogicException('This binding does not require resolution');
        
        return $this->GenerateResolutionRequirements();
    }
    protected abstract function GenerateResolutionRequirements();
    
    final public function ResolveRequirements(BindingResolutionRequirements $ResolvedRequirements) {
        $this->VerifyInitialized();
        if(!$this->RequiresResolution())
            throw new \LogicException('This binding does not require resolution');
        
        $this->ResolveResolutionRequirements($ResolvedRequirements);
    }
    protected abstract function ResolveResolutionRequirements
            (BindingResolutionRequirements $ResolvedRequirements);
    
    final public function GetInstance() {
        $this->VerifyInitialized();
        if($this->RequiresResolution())
            throw new \LogicException
                    ('Binding cannot generate instance: Requirements are not resolved');
        
        return $this->GenerateInstance();
    }
    protected abstract function GenerateInstance();
}

?>
