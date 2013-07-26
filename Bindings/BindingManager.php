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
    
    public function GetMatchedBinding($Class, $ParentType) {
        $MatchedBinding = null;
        foreach ($this->Bindings as $Binding) {
            if($Binding->GetParentType() === $ParentType) {
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
        if(!$Binding->RequiresResolution())
            return $Binding->GetInstance();
        
        $ResolutionRequirements = $Binding->GetResolutionRequirements();
        foreach ($ResolutionRequirements->GetRequiredTypes() as $RequiredType) {
            $MatchedBinding = $this->GetMatchedBinding($Binding->BoundTo(), 
                    $RequiredType->getName());
            if($MatchedBinding === null) {
                throw new Exceptions\UnresolveableClassException($Binding->BoundTo(), 
                        'Could not find a suitable binding for constructor parameter: ' . 
                        $RequiredType->getName());
            }
            $MatchedBindingInstanceFactory = function () use (&$MatchedBinding) {
                return $this->ResolveBinding($MatchedBinding);  
            };
            $ResolutionRequirements->ResolveRequiredType($RequiredType, 
                    $MatchedBindingInstanceFactory);
        }
        
        $Binding->ResolveRequirements($ResolutionRequirements);
        return $Binding->GetInstance();
    }
}

?>
