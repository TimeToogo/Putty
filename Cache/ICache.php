<?php

namespace Putty\Cache;

interface ICache {
    public function Save($Key, $Value, $ExpirySeconds = false, $Overwrite = true);
    public function Contains($Key);
    public function Retrieve($Key);
    public function Delete($Key);
}

?>
