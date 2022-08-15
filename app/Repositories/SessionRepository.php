<?php

namespace App\Repositories;

use App\Models\Session;
use App\Repositories\Interfaces\SessionRepositoryInterface;

class SessionRepository implements SessionRepositoryInterface
{
    private Session $session;

    /**
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function getSessionByIdentifier(string $identifier): ?Session
    {
        return $this->session->where('identifier', $identifier)->first();
    }

    public function getOrCreateSession(string $identifier, string $userAgent): Session
    {
        $session = $this->session->firstOrNew([
            'identifier' => $identifier,
        ]);

        $session->user_agent = $userAgent;
        $session->last_activity = now();
        $session->save();

        return $session;
    }
}
