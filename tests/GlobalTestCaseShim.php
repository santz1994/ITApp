<?php

// Compatibility shim: provide a global TestCase class that proxies to
// the namespaced Tests\TestCase. Some legacy tests or third-party
// libraries may reference \TestCase (global); defining this class
// ensures those references resolve correctly via the composer
// classmap autoloader.

if (! class_exists('TestCase')) {
    class TestCase extends \Tests\TestCase
    {
        // Intentionally empty — inherits behavior from Tests\TestCase
    }
}
