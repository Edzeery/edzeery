<?php

it('returns a successful response', function () {
    $response = $this->get("http://" . config('app.domain') . "/");

    $response->assertStatus(200);
});