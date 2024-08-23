<?php

namespace App\Services\ControlPanels;

use GuzzleHttp\Client;

class DirectAdminClient
{
    protected $client;
    protected $apiUrl;
    protected $apiToken;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiUrl = config('services.directadmin.api_url');
        $this->apiToken = config('services.directadmin.api_token');
    }

    public function createAccount($username, $domain, $package)
    {
        // Implement DirectAdmin API call to create account
        // This is a placeholder and should be replaced with actual DirectAdmin API implementation
        $response = $this->client->post($this->apiUrl . '/accounts', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiToken,
            ],
            'json' => [
                'username' => $username,
                'domain' => $domain,
                'package' => $package,
            ],
        ]);

        return $response->getStatusCode() == 201;
    }

    public function suspendAccount($username)
    {
        // Implement DirectAdmin API call to suspend account
        // This is a placeholder and should be replaced with actual DirectAdmin API implementation
        $response = $this->client->post($this->apiUrl . '/accounts/' . $username . '/suspend', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiToken,
            ],
        ]);

        return $response->getStatusCode() == 200;
    }

    public function unsuspendAccount($username)
    {
        // Implement DirectAdmin API call to unsuspend account
        // This is a placeholder and should be replaced with actual DirectAdmin API implementation
        $response = $this->client->post($this->apiUrl . '/accounts/' . $username . '/unsuspend', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiToken,
            ],
        ]);

        return $response->getStatusCode() == 200;
    }

    public function changePackage($username, $newPackage)
    {
        // Implement DirectAdmin API call to change package
        // This is a placeholder and should be replaced with actual DirectAdmin API implementation
        $response = $this->client->put($this->apiUrl . '/accounts/' . $username . '/package', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiToken,
            ],
            'json' => [
                'package' => $newPackage,
            ],
        ]);

        return $response->getStatusCode() == 200;
    }
}