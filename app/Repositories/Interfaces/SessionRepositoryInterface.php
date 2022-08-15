<?php

namespace App\Repositories\Interfaces;

use App\Models\Session;

interface SessionRepositoryInterface
{
    public function getSessionByIdentifier(string $identifier): ?Session;

    public function getOrCreateSession(string $identifier, string $userAgent): Session;
}
