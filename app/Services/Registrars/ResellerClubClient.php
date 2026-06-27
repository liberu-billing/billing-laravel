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

    /**
     * @return array<int, array{id: string, type: string, name: string, content: string, ttl: int}>
     */
    public function getDnsRecords(string $domainName): array
    {
        // ponytail: real registrar call here — ResellerClub dns/manage/search-records.
        return [];
    }

    /**
     * @param  array{type: string, name: string, content: string, ttl?: int}  $record
     */
    public function addDnsRecord(string $domainName, array $record): bool
    {
        // ponytail: real registrar call here — ResellerClub dns/manage/add-<type>-record.
        return true;
    }

    public function deleteDnsRecord(string $domainName, string $recordId): bool
    {
        // ponytail: real registrar call here — ResellerClub dns/manage/delete-record.
        return true;
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function getWhoisContacts(string $domainName): array
    {
        // ponytail: real registrar call here — ResellerClub domains/details (contact ids).
        return [];
    }

    /**
     * @param  array<string, array<string, string>>  $contacts
     */
    public function updateWhoisContacts(string $domainName, array $contacts): bool
    {
        // ponytail: real registrar call here — ResellerClub domains/modify-contact.
        return true;
    }

    protected function makeApiCall(string $action, $params)
    {
        // Trusted credentials must win over caller-supplied params, so merge them LAST.
        $params = array_merge(
            $params,
            [
                'auth-userid' => $this->authUserId,
                'api-key' => $this->apiKey,
            ]
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
