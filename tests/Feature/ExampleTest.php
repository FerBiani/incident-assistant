<?php

test('redirects the home page to the project list', function () {
    $response = $this->get(route('home'));

    $response->assertRedirectToRoute('projects.index');
});
