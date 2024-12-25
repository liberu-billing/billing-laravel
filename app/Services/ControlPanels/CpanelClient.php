<?php

namespace App\Services\ControlPanels;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use App\Models\HostingServer;

class CpanelClient
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
        $endpoint = '/json-api/createacct';
        $params = [
            'username' => $username,
            'domain' => $domain,
            'plan' => $package,
            'featurelist' => $package,
            'password' => $this->generatePassword(),
            'contactemail' => $username . '@' . $domain,
            'quota' => 0, // Unlimited
            'maxftp' => 'unlimited',
            'maxsql' => 'unlimited',
            'maxpop' => 'unlimited',
            'cpmod' => 'paper_lantern',
            'maxsub' => 'unlimited',
            'maxpark' => 'unlimited',
            'maxaddon' => 'unlimited',
            'bwlimit' => 0, // Unlimited
            'customip' => $this->server->ip_address ?? '',
            'shell' => 'n',
            'owner' => $this->server->username
        ];

        return $this->makeApiCall($endpoint, $params);
    }

    public function suspendAccount($username)
    {
        $endpoint = '/json-api/suspendacct';
        $params = [
            'user' => $username,
            'reason' => 'Non-payment',
            'leave-ftp' => 0
        ];

        return $this->makeApiCall($endpoint, $params);
    }

    public function unsuspendAccount($username)
    {
        $endpoint = '/json-api/unsuspendacct';
        $params = [
            'user' => $username
        ];

        return $this->makeApiCall($endpoint, $params);
    }

    public function changePackage($username, $newPackage)
    {
        $endpoint = '/json-api/changepackage';
        $params = [
            'user' => $username,
            'pkg' => $newPackage
        ];

        return $this->makeApiCall($endpoint, $params);
    }

    public function terminateAccount($username)
    {
        $endpoint = '/json-api/removeacct';
        $params = [
            'user' => $username,
            'keepdns' => 0
        ];

        return $this->makeApiCall($endpoint, $params);
    }

    protected function makeApiCall($endpoint, $params)
    {
        if (!$this->server) {
            throw new \Exception('Server not configured');
        }

        try {
            $response = $this->client->request('GET', 'https://' . $this->server->hostname . ':2087' . $endpoint, [
                'headers' => [
                    'Authorization' => 'WHM ' . $this->server->username . ':' . $this->apiToken,
                ],
                'query' => $params,
                'verify' => false // Only if using self-signed SSL
            ]);

            $result = json_decode($response->getBody(), true);

            if (isset($result['metadata']['result']) && $result['metadata']['result'] === 1) {
                Log::info("cPanel API call successful", [
                    'endpoint' => $endpoint,
                    'server' => $this->server->hostname
                ]);
                return true;
            }
            
            Log::error("cPanel API call failed", [
                'endpoint' => $endpoint, 
                'server' => $this->server->hostname,
                'error' => $result['metadata']['reason'] ?? 'Unknown error'
            ]);
            return false;

        } catch (GuzzleException $e) {
            Log::error("cPanel API call error", [
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