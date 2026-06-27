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
     * @return array{expiration_date: Carbon|null}|null
     */
    public function registerDomain($domainName, $customerId): ?array
    {
        // ponytail: stub — implement eNom API call to register domain
        $this->makeApiCall('Register', ['SLD' => $domainName, 'customerid' => $customerId]);

        return ['expiration_date' => null];
    }

    /**
     * @return array{new_expiration_date: Carbon|null}|null
     */
    public function renewDomain($domainName, $period): ?array
    {
        // ponytail: stub — implement eNom API call to renew domain
        $this->makeApiCall('Extend', ['SLD' => $domainName, 'NumYears' => $period]);

        return ['new_expiration_date' => null];
    }

    /**
     * @return array{expiration_date: Carbon|null}|null
     */
    public function transferDomain($domainName, $authCode, $customerId): ?array
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

    /**
     * @return array<int, array{id: string, type: string, name: string, content: string, ttl: int}>
     */
    public function getDnsRecords(string $domainName): array
    {
        // ponytail: real registrar call here — eNom GetHosts; parse XML into records.
        $this->makeApiCall('GetHosts', ['SLD' => $domainName]);

        return [];
    }

    /**
     * @param  array{type: string, name: string, content: string, ttl?: int}  $record
     */
    public function addDnsRecord(string $domainName, array $record): bool
    {
        // ponytail: real registrar call here — eNom SetHosts (eNom replaces the full host set).
        $this->makeApiCall('SetHosts', array_merge(['SLD' => $domainName], $record));

        return true;
    }

    public function deleteDnsRecord(string $domainName, string $recordId): bool
    {
        // ponytail: real registrar call here — eNom SetHosts minus the record id.
        $this->makeApiCall('SetHosts', ['SLD' => $domainName, 'DeleteHostId' => $recordId]);

        return true;
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function getWhoisContacts(string $domainName): array
    {
        // ponytail: real registrar call here — eNom GetWhoisContact; parse XML into contacts.
        $this->makeApiCall('GetWhoisContact', ['SLD' => $domainName]);

        return [];
    }

    /**
     * @param  array<string, array<string, string>>  $contacts
     */
    public function updateWhoisContacts(string $domainName, array $contacts): bool
    {
        // ponytail: real registrar call here — eNom Contacts (flatten per-contact fields).
        $this->makeApiCall('Contacts', ['SLD' => $domainName]);

        return true;
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
