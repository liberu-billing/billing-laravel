

<?php

namespace App\Services\ControlPanels;

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
        $params = [
            'program' => 'create-domain',
            'domain' => $domain,
            'user' => $username,
            'pass' => $this->generatePassword(),
            'plan' => $package,
            'features' => 'dir,unix,dns,mail,web,webalizer,ssl,mysql,spam,virus',
            'template' => 'default',
            'default-features' => 1,
            'allocate-ip' => 'yes',
            'ip' => $this->server->ip_address ?? '',
            'prefix' => 'www',
            'quota' => 0, // Unlimited
            'uquota' => 0, // Unlimited
            'mysql-pass' => $this->generatePassword(),
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
            'plan' => $newPackage
        ];

        return $this->makeApiCall($params);
    }

    public function terminateAccount($username)
    {
        $params = [
            'program' => 'delete-domain',
            'user' => $username,
            'cleanup' => 1
        ];

        return $this->makeApiCall($params);
    }

    protected function makeApiCall($params)
    {
        if (!$this->server) {
            throw new \Exception('Server not configured');
        }

        try {
            // Virtualmin uses basic auth with username/password or API key
            $response = $this->client->request('GET', 'https://' . $this->server->hostname . ':10000/virtual-server/remote.cgi', [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($this->server->username . ':' . $this->apiKey),
                ],
                'query' => $params,
                'verify' => false // Only if using self-signed SSL
            ]);

            $result = $response->getBody()->getContents();

            // Virtualmin returns error=message on failure
            if (strpos($result, 'error=') === false) {
                Log::info("Virtualmin API call successful", [
                    'program' => $params['program'],
                    'server' => $this->server->hostname
                ]);
                return true;
            }

            preg_match('/error=(.+)/', $result, $matches);
            $error = $matches[1] ?? 'Unknown error';

            Log::error("Virtualmin API call failed", [
                'program' => $params['program'],
                'server' => $this->server->hostname,
                'error' => $error
            ]);
            return false;

        } catch (GuzzleException $e) {
            Log::error("Virtualmin API call error", [
                'program' => $params['program'],
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