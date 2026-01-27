<?php

test('can list clients', function () {
    $response = apiRequest('/clients');
    
    expect($response)->toBeSuccessfulApiResponse();
    expect($response)->toMatchSnapshot();
})->group('clients', 'api');
