<?php

namespace Putty;

PuttyRequireDirectory('Exceptions/*.php');

PuttyRequire('Syntax/FluentConstrainedBindingSyntax.php');
PuttyRequireDirectory('Syntax/*.php');

PuttyRequire('Lifecycles/Lifecycle.php');
PuttyRequireDirectory('Lifecycles/*.php');

PuttyRequire('Bindings/Binding.php');
PuttyRequire('Bindings/ConstrainedBinding.php');
PuttyRequireDirectory('Bindings/*.php');

PuttyRequire('PuttyModule.php');
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
