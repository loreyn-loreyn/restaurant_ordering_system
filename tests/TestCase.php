<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Livewire::test() calls component methods in-process rather than
        // dispatching a real HTTP request through the kernel, so the
        // StartSession middleware never runs and request()->session()
        // throws "Session store not set on request." Any component that
        // touches request()->session() directly (e.g. Login::login()'s
        // regenerate() call, or the various signOut() methods that call
        // invalidate()/regenerateToken()) needs this binding done once,
        // here, so every test class gets it automatically.
        $this->app['request']->setLaravelSession($this->app['session']->driver());
    }
}