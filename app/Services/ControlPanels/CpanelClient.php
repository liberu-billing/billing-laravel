<?php

namespace App\Services\ControlPanels;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use App\Models\HostingServer;

class CpanelClient
{
    protected \GuzzleHttp\Client $client;
    protected $server;
    protected $apiToken;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function setServer(HostingServer $server): void
    {
        $this->server = $server;
        $this->apiToken = $server->api_token;
    }

    public function createAccount(string $username, string $domain, $package): bool
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

    public function suspendAccount($username): bool
    {
        $endpoint = '/json-api/suspendacct';
        $params = [
            'user' => $username,
            'reason' => 'Non-payment',
            'leave-ftp' => 0
        ];

        return $this->makeApiCall($endpoint, $params);
    }

    public function unsuspendAccount($username): bool
    {
        $endpoint = '/json-api/unsuspendacct';
        $params = [
            'user' => $username
        ];

        return $this->makeApiCall($endpoint, $params);
    }

    public function changePackage($username, $newPackage): bool
    {
        $endpoint = '/json-api/changepackage';
        $params = [
            'user' => $username,
            'pkg' => $newPackage
        ];

        return $this->makeApiCall($endpoint, $params);
    }

    public function terminateAccount($username): bool
    {
        $endpoint = '/json-api/removeacct';
        $params = [
            'user' => $username,
            'keepdns' => 0
        ];

        return $this->makeApiCall($endpoint, $params);
    }

    public function addAddon($username, $addon): bool
    {
        // cPanel addons are typically features added to an account
        // This can be done by modifying account features
        $endpoint = '/json-api/modifyacct';
        $params = [
            'user' => $username,
            'FEATURE-' . strtoupper((string) $addon) => 1
        ];

        return $this->makeApiCall($endpoint, $params);
    }

    public function removeAddon($username, $addon): bool
    {
        $endpoint = '/json-api/modifyacct';
        $params = [
            'user' => $username,
            'FEATURE-' . strtoupper((string) $addon) => 0
        ];

        return $this->makeApiCall($endpoint, $params);
    }

    protected function makeApiCall(string $endpoint, $params): bool
    {
        if (!$this->server) {
            throw new Exception('Server not configured');
        }

        $this->validateHostname($this->server->hostname);

        try {
            $response = $this->client->request('GET', 'https://' . $this->server->hostname . ':2087' . $endpoint, [
                'headers' => [
                    'Authorization' => 'WHM ' . $this->server->username . ':' . $this->apiToken,
                ],
                'query' => $params,
                'verify' => config('services.cpanel.ssl_verify', true),
            ]);

            $result = json_decode((string) $response->getBody(), true);

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

    protected function validateHostname(string $hostname): void
    {
        // Reject private/loopback IPs to prevent SSRF
        if (filter_var($hostname, FILTER_VALIDATE_IP)) {
            $isPrivate = !filter_var(
                $hostname,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            );
            if ($isPrivate) {
                throw new Exception('Private or reserved IP addresses are not allowed as cPanel hostnames');
            }
        } elseif (!filter_var($hostname, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            throw new Exception('Invalid cPanel hostname');
        }
    }

    protected function generatePassword(): string
    {
        return bin2hex(random_bytes(12));
    }
}