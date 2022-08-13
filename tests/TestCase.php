<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use function Pest\Faker\faker;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected string $baseUrl;

    protected function setup(): void
    {
        parent::setUp();
        $this->baseUrl = config('app.url') . '/api/carts';
        $this->faker = faker();
    }
}
