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
        array_splice($this->Bindings, array_search($Binding, $this->Bindings), 1);
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
    
    public function GetAllMatchedBindings($Class, $ParentType) {
        $MatchedBindings = array();
        foreach ($this->Bindings as $Binding) {
            $BindingParentType = $Binding->GetParentType();
            if($BindingParentType === $ParentType
                    || is_subclass_of($BindingParentType, $ParentType)) {
                if($Binding->Matches($Class)) {
                    $MatchedBindings[] = $Binding;
                }
                else if ($Binding->ExactlyMatches($Class)) {
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
    
    private function ResolveBindingRescursive(Binding $Binding) {
        if(!$Binding->RequiresResolution()) {
            return $Binding->GetInstance();
        }
        
        $ResolutionRequirements = $Binding->GetResolutionRequirements();
        foreach ($ResolutionRequirements->GetRequiredTypes() as $RequiredType) {
            $QualifiedName = $this->Qualify($RequiredType->getName());
            $MatchedBinding = $this->GetMatchedBinding($Binding->BoundTo(), 
                    $QualifiedName);
            if($MatchedBinding === null) {
                throw new Exceptions\UnresolveableClassException($Binding->BoundTo(), 
                        'Could not find a suitable binding for constructor parameter: ' . 
                        $QualifiedName);
            }
            $MatchedBindingInstanceFactory = function () use ($MatchedBinding) {
                return $this->ResolveBindingRescursive($MatchedBinding);  
            };
            $ResolutionRequirements->ResolveRequiredType($RequiredType, 
                    $MatchedBindingInstanceFactory);
        }
        
        $Binding->ResolveRequirements($ResolutionRequirements);
        return $Binding->GetInstance();
    }
    
    private function Qualify($Type) {
        if($Type[0] !== '\\')
            $Type = '\\' . $Type;
        
        return $Type;
    }
}

?>
