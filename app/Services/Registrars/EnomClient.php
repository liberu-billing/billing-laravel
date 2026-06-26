<?php

namespace App\Services\Registrars;

use GuzzleHttp\Client;
use Illuminate\Support\Carbon;

class EnomClient
{
    protected Client $client;

    protected $apiUrl;

    protected $username;

    protected $password;

    public function __construct()
    {
        $this->client = new Client;
        $this->apiUrl = config('services.enom.api_url');
        $this->username = config('services.enom.username');
        $this->password = config('services.enom.password');
    }

    /**
     * @return array{expiration_date: Carbon|null}
     */
    public function registerDomain($domainName, $customerId): array
    {
        // ponytail: stub — implement eNom API call to register domain
        $this->makeApiCall('Register', ['SLD' => $domainName, 'customerid' => $customerId]);

        return ['expiration_date' => null];
    }

    /**
     * @return array{new_expiration_date: Carbon|null}
     */
    public function renewDomain($domainName, $period): array
    {
        // ponytail: stub — implement eNom API call to renew domain
        $this->makeApiCall('Extend', ['SLD' => $domainName, 'NumYears' => $period]);

        return ['new_expiration_date' => null];
    }

    /**
     * @return array{expiration_date: Carbon|null}
     */
    public function transferDomain($domainName, $authCode, $customerId): array
    {
        // ponytail: stub — implement eNom API call to transfer domain
        $this->makeApiCall('TP_CreateOrder', ['SLD' => $domainName, 'AuthInfo' => $authCode, 'customerid' => $customerId]);

        return ['expiration_date' => null];
    }

    /**
     * @return array<int, string>
     */
    public function getAvailableTlds(): array
    {
        // ponytail: stub — implement eNom GetTLDList API call and parse response
        return [];
    }

    public function getDomainPrice(string $tld): float
    {
        // ponytail: stub — implement eNom GetExtAttributes/pricing API call
        return 0.0;
    }

    protected function makeApiCall($command, $params): void
    {
        $params = array_merge(
            [
                'command' => $command,
                'uid' => $this->username,
                'pw' => $this->password,
                'responsetype' => 'xml',
            ],
            $params
        );

        $this->client->get(
            $this->apiUrl,
            [
                'query' => $params,
            ]
        );

        // Parse XML response and return result
    }
}
