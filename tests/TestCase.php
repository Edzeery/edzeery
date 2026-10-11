<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * A cached `bootstrap/cache/config.php` bakes the real environment, so
     * phpunit.xml's `DB_CONNECTION=sqlite` / `DB_DATABASE=:memory:` overrides
     * become no-ops and `RefreshDatabase` would point at — and truncate — the
     * developer database. Drop the cached config before the kernel boots so the
     * suite always runs against the in-memory connection.
     */
    public function createApplication()
    {
        $cachedConfig = Application::inferBasePath()
            .DIRECTORY_SEPARATOR.'bootstrap'
            .DIRECTORY_SEPARATOR.'cache'
            .DIRECTORY_SEPARATOR.'config.php';

        if (is_file($cachedConfig)) {
            @unlink($cachedConfig);
        }

        return parent::createApplication();
    }
}
