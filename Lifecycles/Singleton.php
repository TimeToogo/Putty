<?php

namespace Putty\Lifecycles;

class Singleton extends Lifecycle {
    private $Instance = null;
    
    public function GetInstance() {
        if($this->Instance === null){
            $Factory = $this->GetInstanceFactory();
            $this->Instance = $Factory();
        }
        return $this->Instance;
    }    
}

?>
