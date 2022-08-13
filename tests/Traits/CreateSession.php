<?php

namespace Tests\Traits;

use App\Models\Session;

trait CreateSession
{
    protected function createSession(int $count = 1)
    {
        return $count > 1 ? Session::factory()->count($count)->create() : Session::factory()->create();
    }
}
