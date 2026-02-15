<?php

namespace App\Services\ControlPanels;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use App\Models\HostingServer;

class LiberuControlPanelClient
{
    protected $client;
    protected $server;
    protected $apiToken;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function setServer(HostingServer $server)
    {
        $this->server = $server;
        $this->apiToken = $server->api_token;
    }

    public function createAccount($username, $domain, $package)
    {
        $password = $this->generatePassword();
        $data = [
            'username' => $username,
            'domain' => $domain,
            'email' => $username . '@' . $domain,
            'password' => $password,
            'package' => $package,
            'status' => 'active'
        ];

        return $this->makeApiCall('POST', '/api/hosting/accounts', $data);
    }

    public function suspendAccount($username)
    {
        $data = [
            'username' => $username,
            'reason' => 'Non-payment'
        ];

        return $this->makeApiCall('POST', '/api/hosting/accounts/' . $username . '/suspend', $data);
    }

    public function unsuspendAccount($username)
    {
        return $this->makeApiCall('POST', '/api/hosting/accounts/' . $username . '/unsuspend', [
            'username' => $username
        ]);
    }

    public function changePackage($username, $newPackage)
    {
        $data = [
            'username' => $username,
            'package' => $newPackage
        ];

        return $this->makeApiCall('PUT', '/api/hosting/accounts/' . $username . '/package', $data);
    }

    public function terminateAccount($username)
    {
        return $this->makeApiCall('DELETE', '/api/hosting/accounts/' . $username, [
            'username' => $username
        ]);
    }

    public function addAddon($username, $addon)
    {
        $data = [
            'username' => $username,
            'addon' => $addon
        ];

        return $this->makeApiCall('POST', '/api/hosting/accounts/' . $username . '/addons', $data);
    }

    public function removeAddon($username, $addon)
    {
        $data = [
            'username' => $username,
            'addon' => $addon
        ];

        return $this->makeApiCall('DELETE', '/api/hosting/accounts/' . $username . '/addons/' . $addon, $data);
    }

    protected function makeApiCall($method, $endpoint, $data = [])
    {
        if (!$this->server) {
            throw new Exception('Server not configured');
        }

        try {
            $options = [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiToken,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'verify' => false
            ];

            if (!empty($data)) {
                $options['json'] = $data;
            }

            $url = rtrim($this->server->api_url, '/') . $endpoint;
            $response = $this->client->request($method, $url, $options);

            $result = json_decode($response->getBody()->getContents(), true);

            if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                Log::info("Liberu Control Panel API call successful", [
                    'method' => $method,
                    'endpoint' => $endpoint,
                    'server' => $this->server->hostname
                ]);
                return true;
            }

            Log::error("Liberu Control Panel API call failed", [
                'method' => $method,
                'endpoint' => $endpoint,
                'server' => $this->server->hostname,
                'error' => $result['message'] ?? 'Unknown error'
            ]);
            return false;

        } catch (GuzzleException $e) {
            Log::error("Liberu Control Panel API call error", [
                'method' => $method,
                'endpoint' => $endpoint,
                'server' => $this->server->hostname,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    protected function generatePassword()
    {
        return bin2hex(random_bytes(12));
    }
}
