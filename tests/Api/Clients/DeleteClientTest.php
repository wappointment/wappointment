<?php

test('can delete a client', function () {
    // First create a client
    $clientData = [
        'name' => 'Client to Delete',
        'email' => 'delete-test-client@test.local',
        'options' => ['source' => 'test']
    ];
    $createResponse = apiRequest('/clients', 'POST', $clientData);
    $clientId = $createResponse['body']['data']['id'];
    
    // Delete the client
    $response = apiRequest("/clients/{$clientId}/delete", 'POST');
    
    expect($response)->toBeSuccessfulApiResponse();
    expect($response)->toMatchSnapshot();
})->group('clients', 'api');
