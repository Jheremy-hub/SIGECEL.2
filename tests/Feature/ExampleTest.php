<?php

uses(Tests\TestCase::class)->in(__DIR__);

test('the application returns a successful response', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
