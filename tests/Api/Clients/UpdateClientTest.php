<?php

test('can update a client', function () {
    // First create a client
    $clientData = [
        'name' => 'Client to Update',
        'email' => 'update-test-client@test.local',
        'options' => ['source' => 'test']
    ];
    $clientId = 5;

    // Update the client
    $updateData = ['name' => 'Updated Name'];
    $response = apiRequest("/clients/{$clientId}/put", 'POST', $updateData);
    
    expect($response)->toBeSuccessfulApiResponse();
    expect(normalizeResponse($response))->toMatchSnapshot();
})->group('clients', 'api');
