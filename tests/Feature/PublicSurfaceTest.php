<?php

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

test('unused account endpoints return 404', function (string $method, string $path) {
    Mail::fake();

    $this->call($method, $path)->assertNotFound();

    Mail::assertNothingSent();
    Mail::assertNothingQueued();
})->with([
    ['GET', '/login'], ['POST', '/login'], ['POST', '/logout'],
    ['GET', '/register'], ['POST', '/register'],
    ['GET', '/forgot-password'], ['POST', '/forgot-password'],
    ['GET', '/reset-password/test-token'], ['POST', '/reset-password'],
    ['GET', '/dashboard'], ['GET', '/settings'],
    ['GET', '/settings/profile'], ['PATCH', '/settings/profile'], ['DELETE', '/settings/profile'],
    ['GET', '/settings/security'], ['PUT', '/settings/password'], ['GET', '/settings/appearance'],
    ['GET', '/user/confirm-password'], ['POST', '/user/confirm-password'],
    ['GET', '/user/confirmed-password-status'], ['GET', '/email/verify'],
    ['POST', '/email/verification-notification'], ['GET', '/two-factor-challenge'],
    ['POST', '/two-factor-challenge'], ['POST', '/user/two-factor-authentication'],
]);

test('no account or Fortify route is registered', function () {
    foreach (Route::getRoutes() as $route) {
        expect($route->getActionName())->not->toContain('Fortify');
        expect($route->uri())->not->toMatch('/^(login|logout|register|dashboard|settings|user|email|two-factor|passkeys|forgot-password|reset-password)(\/|$)/');
    }
});

test('application boot health is available', function () {
    $this->get('/up')->assertOk();
});
