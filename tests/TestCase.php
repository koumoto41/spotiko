<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Sanctum only attaches session/cookie middleware to requests it
        // recognizes as coming from the SPA frontend (via Origin/Referer).
        $this->withHeader('Origin', config('app.url'));
    }
}
