<?php

namespace Putty;

use \Putty\Exceptions;

abstract class PuttyContainer {
    private $Bindings = array();
    
    final public static function Instance() {
        static $Instance = null;
        if($Instance === null)
                $Instance = new static();
        return $Instance;
    }
    
    final private function __construct() {
        $this->Initialize();
    }

    protected abstract function RegisterModules(array &$Registrar);
    
    private function Initialize() {
        $ModuleRegistrar = array();
        $this->RegisterModules($ModuleRegistrar);
        foreach ($ModuleRegistrar as $Module) {
            if(!($Module instanceof PuttyModule))
                throw new Exceptions\InvalidModuleException();
            
            foreach ($Module->GetBindings() as $Binding) {
                $this->AddBinding($Binding);
            }
            
            $this->ResolveSingletons();
        }
    }
    
    private function AddBinding(Binding $Binding) {
        $ParentName = $Binding->GetParentClassOrInterface()->getName();
        foreach ($this->Bindings as $OtherBinding) {
            if($OtherBinding->GetParentClassOrInterface()->getName() === $ParentName) {
                if(!$Binding->IsConstrained() && !$Binding->IsConstrained()){
                    throw new Exceptions\AmbiguousBindingsException(
                            'Multiple unconstrained bindings to type: ' . $ParentName);
                }
            }
        }
        $this->Bindings[] = $Binding;
    }
    
    private function ResolveSingletons() {
        foreach ($this->Bindings as $Binding) {
            if($Binding->IsSingleton()) {
                $Binding->ResolveSingleton($this->ResolveBinding($Binding));
            }
        }
    }
    
    private function GetMatchedBinding($Class,  \ReflectionClass $ParentClassOrInterface) {
        $MatchedBinding = null;
        foreach ($this->Bindings as $Binding) {
            if($Binding->GetParentClassOrInterface()->getName() 
                    === $ParentClassOrInterface->getName()) {
                
                if($Binding->Matches($Class)) {
                    $MatchedBinding = $Binding;
                }
                if ($Binding->ExactlyMatches($Class)) {
                    $MatchedBinding = $Binding;
                    break;
                }
            }
        }
        
        return $MatchedBinding;
    }
    
    public function Resolve($Class) {
        try
        {;
            $Reflection = new \ReflectionClass($Class);
            
            $MatchedBinding = $this->GetMatchedBinding(null, $Reflection);
            if($MatchedBinding !== null)
                return $this->ResolveBinding($MatchedBinding);
            
            if(!$Reflection->isInstantiable())
                throw new Exceptions\UnresolveableClassException($Reflection->getName());

            $ConstructorInfo = $Reflection->getConstructor();
            $ConstructorParameters = $ConstructorInfo->getParameters();

            $ResolvedConstructorParameters = $this->ResolveConstructorParameters
                    ($Reflection, $ConstructorParameters);

            return $Reflection->newInstanceArgs($ResolvedConstructorParameters);
        }
        catch (ReflectionException $ReflectionException) {
            throw new Exceptions\UnresolveableClassException($Class, null, $ReflectionException);
        }
    }
    
    private function ResolveBinding(Binding $Binding) {
        if($Binding->IsSingleton())
            return $Binding->GetSingletonInstance();
        
        $Reflection = $Binding->BoundTo();

        $ConstructorInfo = $Reflection->getConstructor();
        if($ConstructorInfo === null)
            return $Reflection->newInstance();
        
        $ConstructorParameters = $ConstructorInfo->getParameters();

        $ResolvedConstructorParameters = $this->ResolveConstructorParameters
                ($Reflection, $ConstructorParameters, $Binding->GetConstantConstructorArgs());

        return $Reflection->newInstanceArgs($ResolvedConstructorParameters);
    }
    
    private function ResolveConstructorParameters(\ReflectionClass $Reflection, array $ConstructorParameters, 
            array $ConstantConstructorParameters = array()) {
        $ResolvedConstructorParameters = array();
        foreach ($ConstructorParameters as $ConstructorParameter) {
            
            if(array_key_exists($ConstructorParameter->name, $ConstantConstructorParameters)) {
                $ResolvedConstructorParameters[] = 
                        $ConstantConstructorParameters[$ConstructorParameter->name];
                
                continue;
            }
            
            if($ConstructorParameter->isOptional())
                continue;
            
            $ParameterType = $ConstructorParameter->getClass();
            if($ParameterType === null)
                throw new Exceptions\UnresolveableClassException($Reflection->getName(), 
                        'There is no defined parameter type or default value for constructor 
                            parameter: ' . $ConstructorParameter->name);
            
            $MatchedBinding = $this->GetMatchedBinding($Reflection->getName(), $ParameterType);
            if($MatchedBinding === null)
                throw new Exceptions\UnresolveableClassException($Reflection->getName(), 
                        'Could not find a suitable binding for constructor parameter: ' . 
                        $ParameterType->getName());
            
            $ResolvedInstance = $this->ResolveBinding($MatchedBinding);
            $ResolvedConstructorParameters[] = $ResolvedInstance;
        }
        
        return $ResolvedConstructorParameters;
    }
}

?>
