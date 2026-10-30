<?php

use Inertia\Testing\AssertableInertia as Assert;

test('guests can open the public foundation without account props', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('welcome')
            ->where('name', config('app.name'))
            ->missing('auth')
            ->missing('sidebarOpen'));
});

test('home responds using the Inertia protocol', function () {
    $version = $this->get(route('home'))->viewData('page')['version'];

    $this->get(route('home'), [
        'X-Inertia' => 'true',
        'X-Inertia-Version' => $version,
        'X-Requested-With' => 'XMLHttpRequest',
    ])
        ->assertOk()
        ->assertHeader('X-Inertia', 'true')
        ->assertJsonPath('component', 'welcome')
        ->assertJsonPath('props.name', config('app.name'));
});
