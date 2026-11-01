<?php

test('the root address leads to the home page', function () {
    $response = $this->get(route('home'));

    $response->assertRedirect('/home');
});
