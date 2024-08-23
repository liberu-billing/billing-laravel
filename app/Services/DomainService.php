<?php

namespace App\Services;

use App\Services\Registrars\EnomClient;
use App\Services\Registrars\ResellerClubClient;
use App\Models\Subscription;
use App\Models\HostingAccount;

class DomainService
{
    protected $enomClient;
    protected $resellerClubClient;

    public function __construct(EnomClient $enomClient, ResellerClubClient $resellerClubClient)
    {
        $this->enomClient = $enomClient;
        $this->resellerClubClient = $resellerClubClient;
    }

    public function registerDomain(Subscription $subscription, $domainName, $registrar = 'enom')
    {
        $client = $this->getClientForRegistrar($registrar);
        $result = $client->registerDomain($domainName, $subscription->customer->id);

        if ($result) {
            $subscription->domain_name = $domainName;
            $subscription->domain_registrar = $registrar;
            $subscription->domain_expiration_date = $result['expiration_date'];
            $subscription->save();

            // Update or create HostingAccount
            $hostingAccount = HostingAccount::updateOrCreate(
                ['subscription_id' => $subscription->id],
                ['domain' => $domainName]
            );
        }

        return $result;
    }

    public function renewDomain(Subscription $subscription, $period = 1)
    {
        $client = $this->getClientForRegistrar($subscription->domain_registrar);
        $result = $client->renewDomain($subscription->domain_name, $period);

        if ($result) {
            $subscription->domain_expiration_date = $result['new_expiration_date'];
            $subscription->save();
        }

        return $result;
    }

    public function transferDomain(Subscription $subscription, $domainName, $authCode, $newRegistrar)
    {
        $client = $this->getClientForRegistrar($newRegistrar);
        $result = $client->transferDomain($domainName, $authCode, $subscription->customer->id);

        if ($result) {
            $subscription->domain_name = $domainName;
            $subscription->domain_registrar = $newRegistrar;
            $subscription->domain_expiration_date = $result['expiration_date'];
            $subscription->save();

            // Update HostingAccount
            $hostingAccount = HostingAccount::where('subscription_id', $subscription->id)->first();
            if ($hostingAccount) {
                $hostingAccount->domain = $domainName;
                $hostingAccount->save();
            }
        }

        return $result;
    }

    protected function getClientForRegistrar($registrar)
    {
        switch ($registrar) {
            case 'enom':
                return $this->enomClient;
            case 'resellerclub':
                return $this->resellerClubClient;
            default:
                throw new \Exception("Unsupported domain registrar: $registrar");
        }
    }
}