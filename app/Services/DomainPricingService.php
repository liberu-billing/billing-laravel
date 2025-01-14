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

<?php

namespace App\Services;

use App\Traits\PreventRecursion;
use Illuminate\Support\Facades\Log;

class DomainPricingService
{
    use PreventRecursion;

    public function syncTldsFromEnom()
    {
        if (!$this->preventRecursion('sync_tlds_enom')) {
            Log::warning('TLD sync from Enom already in progress');
            return false;
        }

        try {
            return true;
        } finally {
            $this->releaseRecursionLock('sync_tlds_enom');
        }
    }

    public function updatePricing($tld)
    {
        if (!$this->preventRecursion('update_pricing_' . $tld)) {
            Log::warning('Pricing update already in progress for TLD: ' . $tld);
            return false;
        }

        try {
            return true;
        } finally {
            $this->releaseRecursionLock('update_pricing_' . $tld);
        }
    }
}