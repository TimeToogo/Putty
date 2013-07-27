<?php

namespace Putty\Bindings;

use \Putty\Exceptions;

class BindingManager {
    private $Bindings = array();
    
    public function AddBinding(Binding $Binding) {
        $this->VerifyNotAmbiguousBinding($Binding);
        $this->Bindings[] = $Binding;
    }
    
    public function RemoveBinding(Binding $Binding) {
        $this->Bindings = array_splice($this->Bindings, array_search($Binding, $this->Bindings), 1);
    }
    
    private function VerifyNotAmbiguousBinding(Binding $Binding) {
        $ParentName = $Binding->GetParentType();
        foreach ($this->Bindings as $OtherBinding) {
            if($OtherBinding->GetParentType() === $ParentName) {
                if(!$Binding->IsConstrained() && !$Binding->IsConstrained()){
                    throw new Exceptions\AmbiguousBindingsException(
                            'Multiple unconstrained bindings to type: ' . $ParentName);
                }
            }
        }
    }
    
    public function GetAllMatchedBinding($Class, $ParentType) {
        $MatchedBindings = array();
        foreach ($this->Bindings as $Binding) {
            $BindingParentType = $Binding->GetParentType();
            if($BindingParentType === $ParentType
                    || is_subclass_of($BindingParentType, $ParentType)) {
                if($Binding->Matches($Class)) {
                    $MatchedBindings[] = $Binding;
                }
                if ($Binding->ExactlyMatches($Class)) {
                    $MatchedBindings[] = $Binding;
                }
            }
        }
        
        return $MatchedBindings;
    }
    
    public function GetMatchedBinding($Class, $ParentType) {
        $MatchedBinding = null;
        foreach ($this->Bindings as $Binding) {
            $BindingParentType = $Binding->GetParentType();
            if($BindingParentType === $ParentType
                    || is_subclass_of($BindingParentType, $ParentType)) {
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
    
    public function ResolveBinding(Binding $Binding) {
        return $this->ResolveBindingRescursive($Binding);
    }    
    
    private function ResolveBindingRescursive(Binding $Binding, array &$DependencyTrace = array()) {        
        $this->VerifyNonCircularDependencies($DependencyTrace);
        
        if(!$Binding->RequiresResolution())
            return $Binding->GetInstance();
        
        $ResolutionRequirements = $Binding->GetResolutionRequirements();
        foreach ($ResolutionRequirements->GetRequiredTypes() as $RequiredType) {
            $DependencyTrace[] = $RequiredType->getName();
            
            $MatchedBinding = $this->GetMatchedBinding($Binding->BoundTo(), 
                    $RequiredType->getName());
            if($MatchedBinding === null) {
                throw new Exceptions\UnresolveableClassException($Binding->BoundTo(), 
                        'Could not find a suitable binding for constructor parameter: ' . 
                        $RequiredType->getName());
            }
            $MatchedBindingInstanceFactory = function () use (&$MatchedBinding, 
                    &$DependencyTrace) {
                return $this->ResolveBindingRescursive($MatchedBinding, $DependencyTrace);  
            };
            $ResolutionRequirements->ResolveRequiredType($RequiredType, 
                    $MatchedBindingInstanceFactory);
        }
        
        $Binding->ResolveRequirements($ResolutionRequirements);
        return $Binding->GetInstance();
    }
    
    private function VerifyNonCircularDependencies (array $DependencyTrace) {
        $HasCircularDependency = count(array_unique($DependencyTrace)) !== count($DependencyTrace);
        if($HasCircularDependency)
            throw new Exceptions\CircularDependencyException($DependencyTrace);
    }
}

?>
