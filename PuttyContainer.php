<?php

namespace Putty;

use \Putty\Exceptions;

abstract class PuttyContainer {
    use Syntax\ModuleRegistrationSyntax;
    
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
        $this->Initialize();
    }

    protected abstract function RegisterModules();
    
    private function Initialize() {
        $this->RegisterModules();
        foreach ($this->Modules as $Module) {
            if(!($Module instanceof PuttyModule))
                throw new Exceptions\InvalidModuleException();
            
            foreach ($Module->GetBindings() as $Binding) {
                $this->BindingManager->AddBinding($Binding);
            }
        }
    }
    
    public function &Resolve($Type) {
        try
        {
            $MatchedBinding = $this->BindingManager->GetMatchedBinding(null, $Type);
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
    
    public function GetAll($ParentType) {
        $MatchedBindings = $this->BindingManager->GetAllMatchedBindings(null, $ParentType);
        $Resolutions = array();
        foreach ($MatchedBindings as $Binding) {
            $Resolutions[] = $this->BindingManager->RemoveBinding($Binding);
        }
        
        return $Resolutions;
    }
}

?>
