<?php

namespace Putty\Lifecycles;

class Transient extends Lifecycle {
    public function GetInstance() {
        $Factory = $this->GetInstanceFactory();
        return $Factory();
    }
}

?>
