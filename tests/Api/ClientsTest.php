<?php

describe('Clients API', function () {

    test('can list clients', function () {
        $response = apiRequest('/clients');
        
        expect($response)->toBeSuccessfulApiResponse();
        expect($response['body']['data'])->toHaveKey('data');
        expect($response['body']['data'])->toHaveKey('total');
    })->group('clients', 'api');

    test('can create a client', function () {
        $clientData = [
            'name' => 'Test Client ' . time(),
            'email' => 'test' . time() . '@example.com',
            'options' => ['source' => 'test']
        ];
        
        $response = apiRequest('/clients', 'POST', $clientData);
        
        expect($response)->toBeSuccessfulApiResponse();
        expect($response['body'])->toHaveKey('success', true);
        expect($response['body'])->toHaveKey('data');
        expect($response['body']['data'])->toHaveKey('id');
    })->group('clients', 'api');

    test('can update a client', function () {
        // First create a client
        $clientData = [
            'name' => 'Client to Update ' . time(),
            'email' => 'update' . time() . '@example.com',
            'options' => ['source' => 'test']
        ];
        $createResponse = apiRequest('/clients', 'POST', $clientData);
        $clientId = $createResponse['body']['data']['id'];
        
        // Update the client
        $updateData = ['name' => 'Updated Name'];
        $response = apiRequest("/clients/{$clientId}/put", 'POST', $updateData);
        
        expect($response)->toBeSuccessfulApiResponse();
        expect($response['body'])->toHaveKey('success', true);
        expect($response['body']['data']['name'])->toBe('Updated Name');
    })->group('clients', 'api');

    test('can delete a client', function () {
        // First create a client
        $clientData = [
            'name' => 'Client to Delete ' . time(),
            'email' => 'delete' . time() . '@example.com',
            'options' => ['source' => 'test']
        ];
        $createResponse = apiRequest('/clients', 'POST', $clientData);
        $clientId = $createResponse['body']['data']['id'];
        
        // Delete the client
        $response = apiRequest("/clients/{$clientId}/delete", 'POST');
        
        expect($response)->toBeSuccessfulApiResponse();
        expect($response['body'])->toHaveKey('success', true);
    })->group('clients', 'api');

    test('returns error for invalid client creation', function () {
        $response = apiRequest('/clients', 'POST', ['name' => '']); // Missing email
        
        expect($response)->toBeApiError(400);
    })->group('clients', 'api');

    test('can search clients', function () {
        // Create a client with specific name
        $searchTerm = 'SearchableClient' . time();
        $clientData = [
            'name' => $searchTerm,
            'email' => 'searchable' . time() . '@example.com',
            'options' => ['source' => 'test']
        ];
        apiRequest('/clients', 'POST', $clientData);
        
        // Search for the client
        $response = apiRequest('/clients?search=' . urlencode($searchTerm));
        
        expect($response)->toBeSuccessfulApiResponse();
        expect($response['body']['data'])->not->toBeEmpty();
    })->group('clients', 'api');
});
