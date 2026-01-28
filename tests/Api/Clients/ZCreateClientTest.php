<?php

test('can create a client', function () {
    $clientData = [
        'name' => 'Test Client',
        'email' => 'test-new-client@test.local',
        'options' => ['source' => 'test']
    ];
    
    $response = apiRequest('/clients', 'POST', $clientData);
    
    expect($response)->toBeSuccessfulApiResponse();
    expect(normalizeResponse($response))->toMatchSnapshot();
})->group('clients', 'api');
