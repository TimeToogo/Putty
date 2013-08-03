<?php

namespace Putty\Bindings;

use \Putty\Exceptions;
use \Putty\Lifecycles;

class ClassBinding extends ConstrainedBinding {
    private $ClassReflection = null;
    private $Lifecycle = null;
    private $ConstantConstructorArgs = array();
    
    public function __construct($ParentType, $BoundTo, 
            array $ConstantConstructorArgs = array(),
            Lifecycles\Lifecycle $Lifecycle = null,
            array $WhenInjectedIntoParentClasses = array(),
            array $WhenInjectedIntoExactClasses = array()) {
        
        parent::__construct($ParentType, $BoundTo,
                $WhenInjectedIntoParentClasses, 
                $WhenInjectedIntoExactClasses);

        if($Lifecycle === null)
            $Lifecycle = new Lifecycles\Transient();
        $this->SetLifecycle($Lifecycle);
        
        $this->SetConstantConstructorArgs($ConstantConstructorArgs);
    }
    
    protected function SetBoundTo($BoundTo) {
        $this->ClassReflection = new \ReflectionClass($BoundTo);
        if(!$this->ClassReflection->isInstantiable())
            throw new Exceptions\InvalidBindingException(
                    $BoundTo . ' must be instantiable');

        if(!$this->ClassReflection->isSubclassOf($this->GetParentType()) 
                && $this->GetParentType() !== $BoundTo)
            throw new Exceptions\InvalidBindingException(
                    $BoundTo . ' is not a valid subclass of' . $this->GetParentType());
        
        parent::SetBoundTo($BoundTo);
    }
    
    protected function GetLifecycle() {
        return $this->Lifecycle;
    }
    public function SetLifecycle(Lifecycles\Lifecycle $Lifecycle) {
        $this->Lifecycle = $Lifecycle;
    }
    
    public function GetConstantConstructorArgs() {
        return $this->ConstantConstructorArgs;
    }
    public function SetConstantConstructorArgs(array $ConstantConstructorArgs) {
        $this->ConstantConstructorArgs = $ConstantConstructorArgs;
    }
    public function AddConstantConstructorArgs($ParameterName, $Value) {
        $this->ConstantConstructorArgs[$ParameterName] = $Value;
    }

    protected function GenerateResolutionRequirements() {
        $Requirements = new BindingResolutionRequirements();
        $RequiredConstructorParameters = $this->GetRequiredConstrutorParameters();
        if($RequiredConstructorParameters === null)
            return $Requirements;
        
        foreach ($RequiredConstructorParameters as $ConstructorParameter) {
            $ParameterType = $ConstructorParameter->getClass();
            if($ParameterType === null)
                throw new Exceptions\UnresolveableClassException($this->BoundTo(), 
                        'There is no defined parameter type or default value for constructor 
                            parameter: ' . $ConstructorParameter->name);
            
            $Requirements->AddRequiredType($ParameterType);
        }
        
        return $Requirements;
    }

    public function RequiresResolution() {
        return !$this->GetLifecycle()->IsResolved();
    }

    protected function ResolveResolutionRequirements
            (BindingResolutionRequirements $ResolvedRequirements) {
        
        $InstanceFactory = $this->CreateInstanceFactory($ResolvedRequirements);
        $this->GetLifecycle()->ResolveInstanceFactory($InstanceFactory);
    }

    protected function GenerateInstance() {
        return $this->GetLifecycle()->GetInstance();
    }
    
    private function GetConstrutorParameters() {
        $ConstructorInfo = $this->ClassReflection->getConstructor();
        if($ConstructorInfo === null)
            return null;
        return $ConstructorInfo->getParameters();
    }
    
    private function GetRequiredConstrutorParameters() {
        $ConstructorParameters = $this->GetConstrutorParameters();
        if($ConstructorParameters === null)
            return null;
        
        $RequiredConstructorParameters = array();
        foreach ($ConstructorParameters as $ConstructorParameter) {
            if($this->ConstrutorParamterHasConstant($ConstructorParameter->name))
                continue;
            if($ConstructorParameter->isOptional())
                continue;
            
            $RequiredConstructorParameters[] = $ConstructorParameter;
        }
        
        return $RequiredConstructorParameters;
    }
    
    private function CreateInstanceFactory(BindingResolutionRequirements $ResolvedRequirements) {
        $ConstructorParameters = $this->GetConstrutorParameters();
        if(empty($ConstructorParameters))
            return function () {
                return $this->ClassReflection->newInstance();
            };
        $ResolvedConstructorParameters = array();
        $FactoryIndexes = array();
        foreach ($ConstructorParameters as $ConstructorParameter) {
            if($this->ConstrutorParamterHasConstant($ConstructorParameter->name)) {
                $ResolvedConstructorParameters[] = 
                        $this->GetConstantConstructorArgs()[$ConstructorParameter->name];
                continue;
            }
            if($ConstructorParameter->isOptional())
                continue;

            $ParameterType = $ConstructorParameter->getClass();
            $TypeResolutionFactory = $ResolvedRequirements->GetTypeResolution($ParameterType);
            $ResolvedConstructorParameters[] = $TypeResolutionFactory;
            $FactoryIndexes[] = array_search($TypeResolutionFactory, 
                    $ResolvedConstructorParameters);
        }
        
        return function() use (&$ResolvedConstructorParameters, &$FactoryIndexes) {
            $Parameters = $ResolvedConstructorParameters;
            foreach ($FactoryIndexes as $FactoryIndex) {
                $Factory = $ResolvedConstructorParameters[$FactoryIndex];
                $Instance = $Factory();
                $Parameters[$FactoryIndex] = $Instance;
            }
            
            return $this->ClassReflection->newInstanceArgs($Parameters);
        };
    }
    
    private function ConstrutorParamterHasConstant($Name) {
        return array_key_exists($Name, $this->GetConstantConstructorArgs());
    }
}

?>
