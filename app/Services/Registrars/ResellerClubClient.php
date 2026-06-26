<?php

namespace App\Services\Registrars;

use GuzzleHttp\Client;
use Illuminate\Support\Carbon;

class ResellerClubClient
{
    protected Client $client;

    protected $apiUrl;

    protected $authUserId;

    protected $apiKey;

    public function __construct()
    {
        $this->client = new Client;
        $this->apiUrl = config('services.resellerclub.api_url');
        $this->authUserId = config('services.resellerclub.auth_userid');
        $this->apiKey = config('services.resellerclub.api_key');
    }

    /**
     * @return array{expiration_date: Carbon|null}|null
     */
    public function registerDomain($domainName, $customerId): ?array
    {
        // ponytail: stub — implement ResellerClub API call to register domain
        return ['expiration_date' => null];
    }

    /**
     * @return array{new_expiration_date: Carbon|null}|null
     */
    public function renewDomain($domainName, $period): ?array
    {
        // ponytail: stub — implement ResellerClub API call to renew domain
        return ['new_expiration_date' => null];
    }

    /**
     * @return array{expiration_date: Carbon|null}|null
     */
    public function transferDomain($domainName, $authCode, $customerId): ?array
    {
        // ponytail: stub — implement ResellerClub API call to transfer domain
        return ['expiration_date' => null];
    }

    protected function makeApiCall(string $action, $params)
    {
        $params = array_merge(
            [
                'auth-userid' => $this->authUserId,
                'api-key' => $this->apiKey,
            ],
            $params
        );

        $this->client->post(
            $this->apiUrl.$action,
            [
                'form_params' => $params,
            ]
        );

        // Parse JSON response and return result
    }
}
