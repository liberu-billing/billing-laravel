<?php

namespace App\Services\ControlPanels;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use App\Models\HostingServer;

class DirectAdminClient
{
    protected $client;
    protected $server;
    protected $loginKey;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function setServer(HostingServer $server)
    {
        $this->server = $server;
        $this->loginKey = $server->api_token;
    }

    public function createAccount($username, $domain, $package)
    {
        $password = $this->generatePassword();
        $params = [
            'action' => 'create',
            'add' => 'Submit',
            'username' => $username,
            'email' => $username . '@' . $domain,
            'passwd' => $password,
            'passwd2' => $password,
            'domain' => $domain,
            'package' => $package,
            'ip' => $this->server->ip_address,
            'notify' => 'no',
            'ssl' => 'ON',
            'cgi' => 'ON',
            'php' => 'ON',
            'spam' => 'ON',
            'quota' => 'unlimited',
            'bandwidth' => 'unlimited',
            'nemailf' => 'unlimited',
            'nemailml' => 'unlimited',
            'nemailr' => 'unlimited',
            'mysql' => 'ON',
            'nsubdomains' => 'unlimited',
            'dns' => 'ON'
        ];

        return $this->makeApiCall('/CMD_API_ACCOUNT_USER', $params);
    }

    public function suspendAccount($username)
    {
        $params = [
            'action' => 'suspend',
            'select0' => $username,
            'suspend_reason' => 'Non-payment'
        ];

        return $this->makeApiCall('/CMD_API_SELECT_USERS', $params);
    }

    public function unsuspendAccount($username)
    {
        $params = [
            'action' => 'unsuspend',
            'select0' => $username
        ];

        return $this->makeApiCall('/CMD_API_SELECT_USERS', $params);
    }

    public function changePackage($username, $newPackage)
    {
        $params = [
            'action' => 'package',
            'user' => $username,
            'package' => $newPackage
        ];

        return $this->makeApiCall('/CMD_API_MODIFY_USER', $params);
    }

    protected function makeApiCall($endpoint, $params)
    {
        if (!$this->server) {
            throw new Exception('Server not configured');
        }

        try {
            $response = $this->client->request('POST', 'https://' . $this->server->hostname . ':2222' . $endpoint, [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($this->server->username . ':' . $this->loginKey),
                ],
                'form_params' => $params,
                'verify' => false
            ]);

            $result = $response->getBody()->getContents();
            parse_str($result, $parsed);

            if (isset($parsed['error']) && $parsed['error'] === '0') {
                Log::info("DirectAdmin API call successful", [
                    'endpoint' => $endpoint,
                    'server' => $this->server->hostname
                ]);
                return true;
            }

            Log::error("DirectAdmin API call failed", [
                'endpoint' => $endpoint,
                'server' => $this->server->hostname,
                'error' => $parsed['text'] ?? $result
            ]);
            return false;

        } catch (GuzzleException $e) {
            Log::error("DirectAdmin API call error", [
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