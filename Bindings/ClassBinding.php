<?php

namespace Putty\Bindings;

use \Putty\Exceptions;
use \Putty\Lifecycles;

class ClassBinding extends ConstrainedBinding {
    private $StoredLifecycle = null;
    private $StoredConstantConstructorArgs = array();
    
    private $ClassReflection = null;
    private $Lifecycle = null;
    private $ConstantConstructorArgs = array();
    
    public function __construct($ParentType, $BoundTo, $LazyLoad = false,
            array $ConstantConstructorArgs = array(),
            Lifecycles\Lifecycle $Lifecycle = null,
            array $WhenInjectedIntoParentClasses = array(),
            array $WhenInjectedIntoExactClasses = array()) {
        
        $this->StoredConstantConstructorArgs = $ConstantConstructorArgs;
        $this->StoredLifecycle = $Lifecycle;
        
        parent::__construct($ParentType, $BoundTo, $LazyLoad,
                $WhenInjectedIntoParentClasses, 
                $WhenInjectedIntoExactClasses);
    }
    
    protected function Initialize() {
        parent::Initialize();
        
        $this->SetLifecycle($this->StoredLifecycle);
        $this->SetConstantConstructorArgs($this->StoredConstantConstructorArgs);
    }
    
    protected function SetBoundTo($BoundTo) {
        if(!$this->IsInitialized())
            return parent::SetBoundTo($BoundTo);
        
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
        if(!$this->IsInitialized())
            return $this->StoredLifecycle;
        return $this->Lifecycle;
    }
    public function SetLifecycle(Lifecycles\Lifecycle $Lifecycle = null) {
        if($Lifecycle === null)
            $Lifecycle = new Lifecycles\Transient();
        if(!$this->IsInitialized())
            return $this->StoredLifecycle = $Lifecycle;
        $this->Lifecycle = $Lifecycle;
    }
    
    public function GetConstantConstructorArgs() {
        if(!$this->IsInitialized())
            return $this->StoredConstantConstructorArgs;
        return $this->ConstantConstructorArgs;
    }
    public function SetConstantConstructorArgs(array $ConstantConstructorArgs) {
        if(!$this->IsInitialized())
            return $this->StoredConstantConstructorArgs = $ConstantConstructorArgs;
        $this->ConstantConstructorArgs = $ConstantConstructorArgs;
    }
    public function AddConstantConstructorArgs($ParameterName, $Value) {
        if(!$this->IsInitialized())
            return $this->StoredConstantConstructorArgs[$ParameterName] = $Value;
        $this->ConstantConstructorArgs[$ParameterName] = $Value;
    }

    protected function GenerateResolutionRequirements() {
        try
        {
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
        catch (\ReflectionException $Exception) {
            throw new Exceptions\UnresolveableClassException($this->BoundTo(), 
                    $Exception->getMessage(), $Exception);
        }
    }

    public function BindingRequiresResolution() {
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
            if($ConstructorParameter->isOptional()) {
                $DefaultValue = $ConstructorParameter->getDefaultValue();
                $ResolvedConstructorParameters[] = $DefaultValue;
                continue;
            }
            
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
