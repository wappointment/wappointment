<?php

test('returns error for invalid client creation', function () {
    $response = apiRequest('/clients', 'POST', ['name' => '']); // Missing email
    
    expect($response)->toBeApiError(400);
    expect($response)->toMatchSnapshot();
})->group('clients', 'api');
