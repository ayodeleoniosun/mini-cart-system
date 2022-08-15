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

test('get valid session by ip address', function () {
    $session = $this->createSession();
    $getSession = $this->sessionRepo->getSessionByIpAddress($session->ip_address);

    $this->assertInstanceOf(Session::class, $getSession);
    $this->assertEquals($session->ip_address, $getSession->ip_address);
});

test('non-existent session record', function () {
    $session = $this->sessionRepo->getSessionByIpAddress('127.0.0.1');
    $this->assertNull($session);
});

test('create new session', function () {
    $ipAddress = '127.0.0.1';
    $userAgent = 'Google chrome';

    $session = $this->sessionRepo->getOrCreateSession($ipAddress, $userAgent);

    $this->assertInstanceOf(Session::class, $session);
    $this->assertEquals($session->ip_address, $ipAddress);
    $this->assertEquals($session->user_agent, $userAgent);
});
