<?php

namespace App\Services;

use App\Models\Tld;
use App\Services\Registrars\EnomClient;
use Exception;

class DomainPricingService
{
    public function __construct(protected EnomClient $enomClient) { }

    public function calculateDomainPrice($domainName)
    {
        $tld = $this->getTldFromDomain($domainName);
        $tldModel = Tld::where(
            'name',
            $tld
        )->first();

        if (!$tldModel) {
            throw new Exception("TLD not supported: $tld");
        }

        return $tldModel->calculatePrice();
    }

    public function syncTldsFromEnom(): void
    {
        $availableTlds = $this->enomClient->getAvailableTlds();

        foreach ($availableTlds as $tldName) {
            $enomPrice = $this->enomClient->getDomainPrice($tldName);

            Tld::updateOrCreate(
                ['name' => $tldName],
                [
                    'enom_cost' => $enomPrice,
                    'base_price' => $enomPrice,
                    'markup_type' => 'percentage',
                    'markup_value' => 10,
                    // Default 10% markup
                ]
            );
        }
    }

    protected function getTldFromDomain($domainName): string
    {
        $parts = explode(
            '.',
            (string)$domainName
        );

        return '.' . end($parts);
    }
}
