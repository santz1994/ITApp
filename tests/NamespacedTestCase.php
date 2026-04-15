<?php

namespace Tests;

// Simple shim so tests that expect \Tests\TestCase will resolve to the
// legacy global TestCase defined in tests/TestCase.php. This keeps both
// old-style global TestCase and namespaced Tests\TestCase working.
class TestCase extends \TestCase
{
    // Intentionally empty - inherits behavior from global TestCase
}
