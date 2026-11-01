<?php

use Illuminate\Support\Facades\Http;

// Only ci-e2e and ci-nightly have a Gotenberg service; both refuse a skipped test (--fail-on-skipped).
$withoutGotenberg = fn (): bool => getenv('GOTENBERG_URL') === false;

test('Gotenberg reports that it is healthy', function () {
    $response = Http::baseUrl(getenv('GOTENBERG_URL'))->get('/health');

    expect($response->successful())->toBeTrue()
        ->and($response->json('status'))->toBe('up')
        ->and($response->json('details.libreoffice.status'))->toBe('up');
})->group('gotenberg')->skip($withoutGotenberg, 'No Gotenberg service in this job.');

test('Gotenberg converts a synthetic file through LibreOffice', function () {
    $response = Http::baseUrl(getenv('GOTENBERG_URL'))
        ->attach('files', 'Dokumen uji sintetis PRANATA.', 'synthetic.txt')
        ->post('/forms/libreoffice/convert');

    expect($response->successful())->toBeTrue()
        ->and(substr($response->body(), 0, 4))->toBe('%NOPE');
})->group('gotenberg')->skip($withoutGotenberg, 'No Gotenberg service in this job.');
