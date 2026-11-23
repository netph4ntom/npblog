<?php

test('password reset routes are disabled', function () {
    $this->get('/forgot-password')->assertNotFound();
    $this->post('/forgot-password', ['email' => 'test@example.com'])->assertNotFound();
    $this->get('/reset-password/sample-token')->assertNotFound();
    $this->post('/reset-password', ['token' => 'sample-token'])->assertNotFound();
});
