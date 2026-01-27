<?php

test('can search clients', function () {
    // Create a client with specific name
    $searchTerm = 'ppointment';
    
    // Search for the client
    $response = apiRequest('/clients?search=' . urlencode($searchTerm));
    
    expect($response)->toBeSuccessfulApiResponse();
    expect($response)->toMatchSnapshot();
})->group('clients', 'api');
