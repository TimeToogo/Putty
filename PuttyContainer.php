<?php

namespace Putty;

use \Putty\Exceptions;

abstract class PuttyContainer {
    
    protected $LazyLoadBindings = false;
    
    private $Cache = null;
    private $CachePrefix;
    private $InitializedCacheKey = 'IsInitialized';
    private $BindingManagerCacheKey = 'BindingManager';
    private $ResolutionBindingsCacheKey = 'ResolutionBindings';
    
    private $BindingManager = null;
    
    final public static function Instance() {
        static $Instance = null;
        if($Instance === null)
                $Instance = new static();
        return $Instance;
    }
    
    final private function __construct() {
        $this->CachePrefix = get_class($this);
        $this->Initialize();
        
        if(!($this->Cache instanceof Cache\ICache))
            $this->Cache = new Cache\ArrayCache();
        
        if($this->Cache->Retrieve($this->InitializedCacheKey)) {
            $this->BindingManager = $this->RetreiveFromCache($this->BindingManagerCacheKey);
        }
        else {
            $this->BindingManager = new Bindings\BindingManager();
            $this->RegisterModules();
            $this->SaveToCache($this->BindingManagerCacheKey, $this->BindingManager);
            $this->SaveToCache($this->InitializedCacheKey, true);
        }
    }
    protected function Initialize() { }
    private function RetreiveFromCache($Key) {
        return $this->Cache->Retrieve($this->CachePrefix . $Key);
    }
    private function ContainsInCache($Key) {
        return $this->Cache->Contains($this->CachePrefix . $Key);
    }
    private function SaveToCache($Key, $Value, $ExpirySeconds = false, $Overwrite = true) {
        return $this->Cache->Save($this->CachePrefix . $Key, $Value, $ExpirySeconds, $Overwrite);
    }
    
    protected abstract function RegisterModules();
    final protected function Register(PuttyModule $Module) {
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
            $this->CacheResolutionBinding($ClassBinding);
            
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
        $BindingCacheKey = $this->ResolutionBindingsCacheKey . $Type;
        if(!$this->ContainsInCache($BindingCacheKey))
            return null;
        
        return $this->RetreiveFromCache($BindingCacheKey);
    }
    
    private function CacheResolutionBinding(Bindings\Binding $Binding) {
        $BindingCacheKey = $this->ResolutionBindingsCacheKey . $Binding->BoundTo();
        
        $this->SaveToCache($BindingCacheKey, $Binding);
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
