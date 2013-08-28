<?php

namespace Putty\Bindings;

use \Putty\Exceptions;

class BindingManager {
    private $Bindings = array();
    
    public function __construct() { }

    public function AddBinding(Binding $Binding) {
        $this->Bindings[] = $Binding;
    }
    
    public function RemoveBinding(Binding $Binding) {
        array_splice($this->Bindings, array_search($Binding, $this->Bindings), 1);
    }
    
    public function GetAllMatchedBindings($Class, $ParentType, $FindSubclasses = false) {
        $MatchedBindings = array();
        foreach ($this->Bindings as $Binding) {
            $BindingParentType = $Binding->GetParentType();
            $IsMatchedClass = $BindingParentType === $ParentType;
            if($FindSubclasses && !$IsMatchedClass)
                $IsMatchedClass = \is_subclass_of($BindingParentType, $Class);
            
            if($IsMatchedClass) {
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
        $MatchedBinding = $this->FindExactBinding($ParentType);
        if($MatchedBinding !== null)
            return $MatchedBinding;
        
        foreach ($this->Bindings as $Binding) {
            $BindingParentType = $Binding->GetParentType();
            if($BindingParentType === $ParentType
                    || is_subclass_of($BindingParentType, $ParentType)) {
                if($Binding->Matches($Class)) {
                    $MatchedBinding = $Binding;
                }
                if ($Binding->ExactlyMatches($Class)) {
                    return $Binding;
                }
            }
        }
        
        return $MatchedBinding;
    }
    
    public function FindExactBinding($ParentType) {
        foreach ($this->Bindings as $Binding) {
            $BindingParentType = $Binding->GetParentType();
            if($BindingParentType === $ParentType && $Binding->ExactlyMatches(null))
                return $Binding;
                
        }
        return null;
    }
    
    public function ResolveBinding(Binding $Binding) {
        $Instance = $this->ResolveBindingRescursive($Binding);
        return $Instance;
    }    
    
    private function ResolveBindingRescursive(Binding $Binding, 
            array &$DependencyMap = null) {
        if(!$Binding->RequiresResolution()) {
            return $Binding->GetInstance();
        }
        
        $CurrentDependencyMap = null;
        if($DependencyMap !== null) {
            $DependencyMap[$Binding->GetParentType()] = array();
            $CurrentDependencyMap = &$DependencyMap[$Binding->GetParentType()];
        }
        $ResolutionRequirements = $Binding->GetResolutionRequirements();
        foreach ($ResolutionRequirements->GetRequiredTypes() as $RequiredType) {
            if($DependencyMap !== null)
                $CurrentDependencyMap[] = $RequiredType->getName();
            
            $QualifiedName = $this->Qualify($RequiredType->getName());
            $MatchedBinding = $this->GetMatchedBinding($Binding->BoundTo(), 
                    $QualifiedName);
            if($MatchedBinding === null) {
                throw new Exceptions\UnresolveableClassException($Binding->BoundTo(), 
                        'Could not find a suitable binding for constructor parameter: ' . 
                        $QualifiedName);
            }
            $MatchedBindingInstanceFactory = function () use ($MatchedBinding, &$CurrentDependencyMap) {
                return $this->ResolveBindingRescursive($MatchedBinding, $CurrentDependencyMap);  
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
