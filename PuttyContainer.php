<?php

namespace Putty;

use \Putty\Exceptions;

abstract class PuttyContainer {
    
    protected $LazyLoadBindings = false;
    
    private $BindingManager = null;
    private $CachedResolutionBindings = array();
    
    final public static function Instance() {
        static $Instance = null;
        if($Instance === null)
                $Instance = new static();
        return $Instance;
    }
    
    final private function __construct() {
        $this->BindingManager = new Bindings\BindingManager();
        $this->RegisterModules();
    }

    protected abstract function RegisterModules();
    
    protected function Register(PuttyModule $Module) {
        foreach ($Module->GetBindings($this->LazyLoadBindings) as $Binding) {
            $this->BindingManager->AddBinding($Binding);
        }
    }
    
    public function Resolve($Type) {
        try
        {
            $MatchedBinding = $this->BindingManager->FindExactBinding($Type);
            if($MatchedBinding !== null)
                return $this->BindingManager->ResolveBinding($MatchedBinding);
            
            $CachedResolutionBinding = $this->GetCachedResolutionBinding($Type);
            if($CachedResolutionBinding !== null)
                return $this->BindingManager->ResolveBinding($CachedResolutionBinding);
            
            $ClassBinding = new Bindings\SelfBinding($Type);
            $ResolvedInstance = $this->BindingManager->ResolveBinding($ClassBinding);
            $this->CachedResolutionBindings[] = $ClassBinding;
            
            return $ResolvedInstance;
        }
        catch (Exceptions\InvalidBindingException $Exception) {
            throw new Exceptions\UnresolveableClassException($Type, null, $Exception);
        }
    }
    
    public function ResolveAll(array $Types) {
        $Resolutions = array();
        foreach ($Types as $Type) {
            $Resolutions[] = $this->Resolve($Type);
        }
        return $Resolutions;
    }
    
    private function GetCachedResolutionBinding($Type) {
        foreach ($this->CachedResolutionBindings as $CachedResolutionBinding) {
            if($CachedResolutionBinding->BoundTo() === $Type)
                return $CachedResolutionBinding;
        }
        return null;
    }
    
    public function GetAll($ParentType, $Subclasses = false) {
        $MatchedBindings = $this->BindingManager->GetAllMatchedBindings(null, $ParentType, $Subclasses);
        $Resolutions = array();
        foreach ($MatchedBindings as $Binding) 
            $Resolutions[] = $this->BindingManager->ResolveBinding($Binding);
        
        return $Resolutions;
    }
}

?>
