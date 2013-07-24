<?php

namespace Putty;

PuttyRequireDirectory('Exceptions/*.php');
PuttyRequireDirectory('Syntax/*.php');

PuttyRequire('Binding.php');
PuttyRequire('PuttyModule.php');
PuttyRequire('PuttyContainer.php');

function PuttyRequire($RelativePath) {
    require_once __DIR__ . '/' . $RelativePath;
}

function PuttyRequireDirectory($RelativePathPattern) {
    foreach (glob(__DIR__ . '/' . $RelativePathPattern) as $File) {
        require_once $File;
    }
}

?>
