<?php

namespace Tests\Unit\Repository;

use App\Models\Session;
use App\Repositories\SessionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
uses()->group('session_repository');

beforeEach(function () {
    $session = new Session();
    $this->sessionRepo = new SessionRepository($session);
});

test('get valid session by identifier', function () {
    $session = $this->createSession();
    $getSession = $this->sessionRepo->getSessionByIdentifier($session->identifier);

    $this->assertInstanceOf(Session::class, $getSession);
    $this->assertEquals($session->identifier, $getSession->identifier);
});

test('non-existent session record', function () {
    $session = $this->sessionRepo->getSessionByIdentifier('12345ab');
    $this->assertNull($session);
});

test('create new session', function () {
    $identifier = 'Str::random(10)';
    $userAgent = 'Google chrome';

    $session = $this->sessionRepo->getOrCreateSession($identifier, $userAgent);

    $this->assertInstanceOf(Session::class, $session);
    $this->assertEquals($session->identifier, $identifier);
    $this->assertEquals($session->user_agent, $userAgent);
});
