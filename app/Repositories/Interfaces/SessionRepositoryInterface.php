<?php

namespace App\Repositories\Interfaces;

use App\Models\Session;

interface SessionRepositoryInterface
{
    public function getSessionByIpAddress(string $ipAddress): Session|null;

    public function getOrCreateSession(string $ipAddress, string $userAgent): Session;
}
