<?php

use Wappointment\Http\JsonResponse;

/**
 * Pest configuration for API tests
 */

// Set WordPress test environment
putenv('WP_TESTS_DIR=/tmp/wordpress-tests-lib');

// Base URL for API calls
define('WP_BASE_URL', 'http://wordpress:80');
define('API_BASE_URL', WP_BASE_URL . '/wp-json/wappointment/v1');

// Test admin credentials (should exist in database snapshot)
define('TEST_USERNAME', 'admin');
define('TEST_PASSWORD', 'password');

/**
 * Get WordPress authentication cookies
 */
function getAuthCookies(): array
{
    static $cookies = null;
    
    if ($cookies === null) {
        // Login to WordPress to get session cookies
        $ch = curl_init(WP_BASE_URL . '/wp-login.php');
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'log' => TEST_USERNAME,
            'pwd' => TEST_PASSWORD,
            'wp-submit' => 'Log In',
            'redirect_to' => WP_BASE_URL . '/wp-admin/',
            'testcookie' => '1'
        ]));
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        // Extract cookies from response headers
        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $response, $matches);
        $cookies = [];
        foreach ($matches[1] as $cookie) {
            $cookies[] = $cookie;
        }
        
        if (empty($cookies)) {
            throw new RuntimeException('Failed to authenticate with WordPress. Check credentials.');
        }
    }
    
    return $cookies;
}

/**
 * Get WordPress nonce for REST API
 */
function getRestNonce(): string
{
    static $nonce = null;
    
    if ($nonce === null) {
        $cookies = getAuthCookies();
        
        // Get nonce from WordPress admin
        $ch = curl_init(WP_BASE_URL . '/wp-admin/admin-ajax.php?action=rest-nonce');
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIE, implode('; ', $cookies));
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        // The response should contain the nonce
        // If admin-ajax.php doesn't work, try getting it from the page
        if (empty($response) || $response === '0') {
            // Get nonce from wp-admin page
            $ch = curl_init(WP_BASE_URL . '/wp-admin/');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_COOKIE, implode('; ', $cookies));
            $html = curl_exec($ch);
            curl_close($ch);
            
            // Extract nonce from wpApiSettings
            if (preg_match('/wpApiSettings.*?"nonce":"([^"]+)"/', $html, $matches)) {
                $nonce = $matches[1];
            }
        } else {
            $nonce = trim($response);
        }
        
        if (empty($nonce)) {
            throw new RuntimeException('Failed to get REST API nonce');
        }
    }
    
    return $nonce;
}

/**
 * Make an authenticated API request
 */
function apiRequest(string $endpoint, string $method = 'GET', array $data = []): array
{
    $url = API_BASE_URL . $endpoint;
    $cookies = getAuthCookies();
    $nonce = getRestNonce();
    
    $ch = curl_init($url);
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_COOKIE, implode('; ', $cookies));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'X-WP-Nonce: ' . $nonce
    ]);
    
    if (!empty($data)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $decoded = json_decode($response, true);
    
    return [
        'status' => $httpCode,
        'body' => $decoded ?? $response,
    ];
}

/**
 * Custom expectation for successful API response
 */
expect()->extend('toBeSuccessfulApiResponse', function () {
    expect($this->value['status'])->toBeGreaterThanOrEqual(200)->toBeLessThan(300);
    expect($this->value['body'])->toBeArray();
    return $this;
});

/**
 * Custom expectation for API error response
 */
expect()->extend('toBeApiError', function (int $expectedStatus = null) {
    if ($expectedStatus) {
        expect($this->value['status'])->toBe($expectedStatus);
    } else {
        expect($this->value['status'])->toBeGreaterThanOrEqual(400);
    }
    return $this;
});
