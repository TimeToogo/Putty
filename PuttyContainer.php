<?php

namespace Putty;

use \Putty\Exceptions;

abstract class PuttyContainer {
    use Syntax\ModuleRegistrationSyntax;
    
    private $BindingManager = null;
    private $CachedResolutions = array();
    
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
    
    public function Resolve($Type) {
        try
        {
            $MatchedBinding = $this->BindingManager->GetMatchedBinding(null, $Class);
            if($MatchedBinding !== null)
                return $this->ResolveBinding($MatchedBinding);
            
            if($CachedResolutionBinding = $this->GetCachedResolution($Type))
                return $this->BindingManager->ResolveBinding($CachedResolutionBinding);
            
            $ClassBinding = new Bindings\SelfBinding($Type);
            $ResolvedInstance = $this->BindingManager->ResolveBinding($ClassBinding);
            $this->CachedResolutions[] = $ClassBinding;
            
            return $ResolvedInstance;
        }
        catch (\InvalidArgumentException $Exception) {
            throw new Exceptions\UnresolveableClassException($Class, null, $Exception);
        }
    }
    
    private function GetCachedResolution($Type) {
        foreach ($this->CachedResolutions as $CachedResolutionBinding) {
            if($CachedResolutionBinding->BoundTo() === $Type)
                return $CachedResolutionBinding;
        }
        return null;
    }
}

?>
