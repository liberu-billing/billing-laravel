<?php

namespace App\Services;

use App\Models\Tld;
use App\Services\Registrars\EnomClient;

class DomainPricingService
{
    protected $enomClient;

    public function __construct(EnomClient $enomClient)
    {
        $this->enomClient = $enomClient;
    }

    public function calculateDomainPrice($domainName)
    {
        $tld = $this->getTldFromDomain($domainName);
        $tldModel = Tld::where('name', $tld)->first();

        if (!$tldModel) {
            throw new \Exception("TLD not supported: $tld");
        }

        return $tldModel->calculatePrice();
    }

    public function syncTldsFromEnom()
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
                    'markup_value' => 10, // Default 10% markup
                ]
            );
        }
    }

    protected function getTldFromDomain($domainName)
    {
        $parts = explode('.', $domainName);
        return '.' . end($parts);
    }
}