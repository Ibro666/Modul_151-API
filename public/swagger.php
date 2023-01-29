<?php
    require("../vendor/autoload.php");
    $openapi = \OpenApi\Generator::scan([__DIR__]);  // in der angegebenen pfad wird nach einem opemapi mokumenten gesucht.
    header('Content-Type: application/x-yaml');
    echo $openapi->toYaml();