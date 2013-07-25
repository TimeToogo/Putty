Putty
=====

A clean and simple dependency injection framework for PHP inspired by C#'s Ninject

Features
========
- Simple yet versatile fluent binding syntax
- Organise bindings into easy to manage modules
- Create loosly coupled systems

Installation
============
Clone repo to your project and simply `require 'Putty\Putty.php';`

Your first Putty Module
======================
Organise your bindings into their own modules:
```php
class TransportationModule extends \Putty\PuttyModule {
    protected function InitializeBindings() {
        $this->Bind('IWheel')->To('RoundWheel')
            ->WhenInjectedInto('IVehicle');
        
        $this->Bind('IVehicle')->To('Car')
            ->WithConstructorArgument('Capacity', 5)
            ->WithConstructorArgument('TopSpeed', 160)
            ->WhenInjectedExactlyInto('MotoringService');
        
        $this->Bind('IVehicle')->To('Train')
            ->WithConstructorArgument('Capacity', 300)
            ->WhenInjectedExactlyInto('PublicTransportService');

        $this->Bind('IVehicle')->To('Plane')
            ->WithConstructorArgument('Capacity', 150)
            ->WhenInjectedInto('IOverseasService');
          
        
        $this->Bind('IVehicleMaintainer')->To('Joe')
            ->WithConstructorArgument('EducationLevel', 'Year 10')
            ->InSingletonLifecycle();
		
        $this->Bind('IMood')->ToConstant(new ShittyMood())
            ->WhenInjectedExactlyInto('Joe');
    }
}
```

Configuring your Putty Container
===============================
Simply extend the PuttyContainer and register your modules
```php
class MyContainer extends \Putty\PuttyContainer{
    protected function RegisterModules() {
        $this->Register(new TransportationModule());
        $this->Register(new BillingModule());
        //...
    }
}
```

Up and running
==============
Now your IOC container is ready to putty your project together...
```php
class PuttyControllerFactory extends IControllerFactory {
    function Resolve($Controller) {
        return MyContainer::Instance()->Resolve($Controller);
    }
}
```
