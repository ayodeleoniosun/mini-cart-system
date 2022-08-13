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

    public function getSessionByIpAddress(string $ipAddress): Session|null
    {
        return $this->session->where('ip_address', $ipAddress)->first();
    }

    public function getOrCreateSession(string $ipAddress, string $userAgent): Session
    {
        $session = $this->getSessionByIpAddress($ipAddress);

        if (!$session) {
            $session = $this->session->create([
                'ip_address'    => $ipAddress,
                'user_agent'    => $userAgent,
                'last_activity' => now()
            ]);
        }

        return $session;
    }
}
