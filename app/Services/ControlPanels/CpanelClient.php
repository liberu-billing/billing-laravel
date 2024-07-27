<?php

namespace App\Services\ControlPanels;

use GuzzleHttp\Client;

class CpanelClient
{
    protected $client;
    protected $apiUrl;
    protected $apiToken;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiUrl = config('services.cpanel.api_url');
        $this->apiToken = config('services.cpanel.api_token');
    }

    public function createAccount($username, $domain, $package)
    {
        // Implement cPanel API call to create account
    }

    public function suspendAccount($username)
    {
        // Implement cPanel API call to suspend account
    }

    public function unsuspendAccount($username)
    {
        // Implement cPanel API call to unsuspend account
    }

    public function changePackage($username, $newPackage)
    {
        // Implement cPanel API call to change package
    }
}