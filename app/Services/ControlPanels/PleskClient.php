<?php

namespace App\Services\ControlPanels;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use App\Models\HostingServer;

class PleskClient
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
        $xml = $this->buildXmlRequest('webspace.add', [
            'gen_setup' => [
                'name' => $domain,
                'owner-login' => $username,
                'owner-password' => $this->generatePassword(),
                'ip_address' => $this->server->ip_address
            ],
            'hosting' => [
                'vrt_hst' => [
                    'property' => [
                        ['name' => 'ftp_login', 'value' => $username],
                        ['name' => 'ftp_password', 'value' => $this->generatePassword()],
                        ['name' => 'php', 'value' => 'true'],
                        ['name' => 'ssl', 'value' => 'true'],
                        ['name' => 'webstat', 'value' => 'awstats'],
                        ['name' => 'www-root', 'value' => "/var/www/vhosts/{$domain}"]
                    ],
                ],
            ],
            'limits' => [
                'overuse' => 'block',
                'limit' => [
                    ['name' => 'disk_space', 'value' => 'unlimited'],
                    ['name' => 'max_traffic', 'value' => 'unlimited'],
                    ['name' => 'max_subdom', 'value' => 'unlimited'],
                    ['name' => 'max_dom', 'value' => 'unlimited'],
                    ['name' => 'max_db', 'value' => 'unlimited'],
                    ['name' => 'max_mail', 'value' => 'unlimited'],
                    ['name' => 'max_wu', 'value' => 'unlimited']
                ]
            ],
            'plan-name' => $package
        ]);

        return $this->makeApiCall($xml);
    }

    public function suspendAccount($username)
    {
        $xml = $this->buildXmlRequest('customer.set', [
            'filter' => ['login' => $username],
            'values' => ['status' => '16'], // 16 is suspended status
            'general' => ['status' => 'suspended']
        ]);

        return $this->makeApiCall($xml);
    }

    public function unsuspendAccount($username)
    {
        $xml = $this->buildXmlRequest('customer.set', [
            'filter' => ['login' => $username],
            'values' => ['status' => '0'], // 0 is active status
            'general' => ['status' => 'active']
        ]);

        return $this->makeApiCall($xml);
    }

    public function changePackage($username, $newPackage)
    {
        $xml = $this->buildXmlRequest('service-plan.set', [
            'filter' => ['owner-login' => $username],
            'values' => ['name' => $newPackage]
        ]);

        return $this->makeApiCall($xml);
    }

    protected function makeApiCall($xml)
    {
        if (!$this->server) {
            throw new \Exception('Server not configured');
        }

        try {
            $response = $this->client->request('POST', 'https://' . $this->server->hostname . ':8443/api/v2/cli/server/', [
                'headers' => [
                    'Content-Type' => 'text/xml',
                    'HTTP_AUTH_KEY' => $this->apiKey,
                    'KEY' => $this->apiKey
                ],
                'body' => $xml,
                'verify' => false
            ]);

            $result = simplexml_load_string($response->getBody()->getContents());

            if ((string)$result->status === 'ok') {
                Log::info("Plesk API call successful", ['server' => $this->server->hostname]);
                return true;
            }

            Log::error("Plesk API call failed", [
                'server' => $this->server->hostname,
                'error' => (string)$result->errtext
            ]);
            return false;

        } catch (GuzzleException $e) {
            Log::error("Plesk API call error", [
                'server' => $this->server->hostname,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    protected function buildXmlRequest($command, $params)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<packet version="1.6.9.1">';
        $xml .= "<{$command}>";
        $xml .= $this->arrayToXml($params);
        $xml .= "</{$command}>";
        $xml .= '</packet>';
        return $xml;
    }

    protected function arrayToXml($array)
    {
        $xml = '';
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (isset($value['name']) && isset($value['value'])) {
                    $xml .= "<{$key} name=\"{$value['name']}\">{$value['value']}</{$key}>";
                } else {
                    $xml .= "<{$key}>" . $this->arrayToXml($value) . "</{$key}>";
                }
            } else {
                $xml .= "<{$key}>{$value}</{$key}>";
            }
        }
        return $xml;
    }

    protected function generatePassword()
    {
        return bin2hex(random_bytes(12));
    }
}