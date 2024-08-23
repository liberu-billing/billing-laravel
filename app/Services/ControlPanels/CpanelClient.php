<?php

namespace App\Services\ControlPanels;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

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
        $endpoint = '/createacct';
        $params = [
            'username' => $username,
            'domain' => $domain,
            'plan' => $package,
        ];

        return $this->makeApiCall($endpoint, $params);
    }

    public function suspendAccount($username)
    {
        $endpoint = '/suspendacct';
        $params = [
            'user' => $username,
        ];

        return $this->makeApiCall($endpoint, $params);
    }

    public function unsuspendAccount($username)
    {
        $endpoint = '/unsuspendacct';
        $params = [
            'user' => $username,
        ];

        return $this->makeApiCall($endpoint, $params);
    }

    public function changePackage($username, $newPackage)
    {
        $endpoint = '/changepackage';
        $params = [
            'user' => $username,
            'pkg' => $newPackage,
        ];

        return $this->makeApiCall($endpoint, $params);
    }

    public function terminateAccount($username)
    {
        $endpoint = '/removeacct';
        $params = [
            'user' => $username,
        ];

        return $this->makeApiCall($endpoint, $params);
    }

    protected function makeApiCall($endpoint, $params)
    {
        try {
            $response = $this->client->request('POST', $this->apiUrl . $endpoint, [
                'headers' => [
                    'Authorization' => 'WHM ' . $this->apiToken,
                ],
                'form_params' => $params,
            ]);

            $result = json_decode($response->getBody(), true);

            if (isset($result['result']) && $result['result'] === 1) {
                Log::info("cPanel API call successful", ['endpoint' => $endpoint, 'params' => $params]);
                return true;
            } else {
                Log::error("cPanel API call failed", ['endpoint' => $endpoint, 'params' => $params, 'response' => $result]);
                return false;
            }
        } catch (GuzzleException $e) {
            Log::error("cPanel API call error", ['endpoint' => $endpoint, 'params' => $params, 'error' => $e->getMessage()]);
            return false;
        }
    }
}