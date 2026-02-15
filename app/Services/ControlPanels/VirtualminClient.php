<?php

namespace App\Services\ControlPanels;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use App\Models\HostingServer;

class VirtualminClient
{
    protected $client;
    protected $server;
    protected $apiKey;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function setServer(HostingServer $server)
    {
        $this->server = $server;
        $this->apiKey = $server->api_token;
    }

    public function createAccount($username, $domain, $package)
    {
        $password = $this->generatePassword();
        $params = [
            'program' => 'create-domain',
            'domain' => $domain,
            'user' => $username,
            'pass' => $password,
            'email' => $username . '@' . $domain,
            'plan' => $package,
            'mysql' => '',
            'web' => '',
            'dns' => '',
            'mail' => '',
            'unix' => ''
        ];

        return $this->makeApiCall($params);
    }

    public function suspendAccount($username)
    {
        $params = [
            'program' => 'disable-domain',
            'user' => $username,
            'why' => 'Non-payment'
        ];

        return $this->makeApiCall($params);
    }

    public function unsuspendAccount($username)
    {
        $params = [
            'program' => 'enable-domain',
            'user' => $username
        ];

        return $this->makeApiCall($params);
    }

    public function changePackage($username, $newPackage)
    {
        $params = [
            'program' => 'modify-domain',
            'user' => $username,
            'apply-plan' => $newPackage
        ];

        return $this->makeApiCall($params);
    }

    public function terminateAccount($username)
    {
        $params = [
            'program' => 'delete-domain',
            'user' => $username
        ];

        return $this->makeApiCall($params);
    }

    public function addAddon($username, $addon)
    {
        $params = [
            'program' => 'modify-domain',
            'user' => $username,
            'enable-feature' => $addon
        ];

        return $this->makeApiCall($params);
    }

    public function removeAddon($username, $addon)
    {
        $params = [
            'program' => 'modify-domain',
            'user' => $username,
            'disable-feature' => $addon
        ];

        return $this->makeApiCall($params);
    }

    protected function makeApiCall($params)
    {
        if (!$this->server) {
            throw new Exception('Server not configured');
        }

        try {
            $params['json'] = '1'; // Request JSON response
            
            $response = $this->client->request('POST', 'https://' . $this->server->hostname . ':10000/virtual-server/remote.cgi', [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($this->server->username . ':' . $this->apiKey),
                ],
                'form_params' => $params,
                'verify' => false
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            if (isset($result['status']) && $result['status'] === 'success') {
                Log::info("Virtualmin API call successful", [
                    'program' => $params['program'],
                    'server' => $this->server->hostname
                ]);
                return true;
            }

            Log::error("Virtualmin API call failed", [
                'program' => $params['program'],
                'server' => $this->server->hostname,
                'error' => $result['error'] ?? 'Unknown error'
            ]);
            return false;

        } catch (GuzzleException $e) {
            Log::error("Virtualmin API call error", [
                'program' => $params['program'] ?? 'unknown',
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
