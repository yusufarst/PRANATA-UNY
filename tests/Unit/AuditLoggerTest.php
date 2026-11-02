<?php

use App\Modules\Audit\Support\AuditLogger;
use Illuminate\Http\Request;

test('keys that name a secret are found at any depth', function () {
    expect(AuditLogger::secretKeys([
        'name' => 'Ani',
        'password' => 'x',
        'profile' => ['twoFactorSecret' => 'x', 'email' => 'ani@example.test'],
        'session' => ['cookie' => 'x', 'items' => [['access_token' => 'x']]],
        'API_KEY' => 'x',
        'headers' => ['X-Api-Key' => 'x', 'Authorization' => 'x'],
    ]))->toBe(['password', 'profile.twoFactorSecret', 'session.cookie', 'session.items.0.access_token', 'API_KEY', 'headers.X-Api-Key', 'headers.Authorization']);
});

test('keys that only contain a secret word inside another word pass', function () {
    expect(AuditLogger::secretKeys([
        'tokenizer' => 'x',
        'secretary_name' => 'x',
        'status' => 'APPROVED',
        'changes' => ['before' => 'a', 'after' => 'b'],
    ]))->toBe([]);
});

test('a request keeps one request id', function () {
    $request = Request::create('/');

    $id = AuditLogger::requestId($request);

    expect(AuditLogger::requestId($request))->toBe($id)
        ->and($request->attributes->get(AuditLogger::REQUEST_ID))->toBe($id);
});

test('a request id that is not a UUID is replaced', function () {
    $request = Request::create('/');
    $request->attributes->set(AuditLogger::REQUEST_ID, 'not-a-uuid');

    expect(AuditLogger::requestId($request))->not->toBe('not-a-uuid');
});
