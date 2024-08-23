<?php

namespace App\Services;

use App\Models\HostingAccount;
use App\Models\Products_Service;
use App\Services\ControlPanels\CpanelClient;
use App\Services\ControlPanels\PleskClient;
use App\Services\ControlPanels\DirectAdminClient;
use App\Services\PricingService;

class HostingService
{
    protected $cpanelClient;
    protected $pleskClient;
    protected $directAdminClient;
    protected $pricingService;

    public function __construct(
        CpanelClient $cpanelClient,
        PleskClient $pleskClient,
        DirectAdminClient $directAdminClient,
        PricingService $pricingService
    ) {
        $this->cpanelClient = $cpanelClient;
        $this->pleskClient = $pleskClient;
        $this->directAdminClient = $directAdminClient;
        $this->pricingService = $pricingService;
    }

    public function provisionAccount(HostingAccount $account, Products_Service $product, array $options = [])
    {
        $client = $this->getClientForControlPanel($account->control_panel);
        $price = $this->pricingService->calculatePrice($product, $options);
        $result = $client->createAccount($account->username, $account->domain, $account->package);
        
        if ($result) {
            $account->status = 'active';
            $account->price = $price;
            $account->save();
        }

        return $result;
    }

    public function suspendAccount(HostingAccount $account)
    {
        $client = $this->getClientForControlPanel($account->control_panel);
        $result = $client->suspendAccount($account->username);
        
        if ($result) {
            $account->status = 'suspended';
            $account->save();
        }

        return $result;
    }

    public function unsuspendAccount(HostingAccount $account)
    {
        $client = $this->getClientForControlPanel($account->control_panel);
        $result = $client->unsuspendAccount($account->username);
        
        if ($result) {
            $account->status = 'active';
            $account->save();
        }

        return $result;
    }

    public function upgradeAccount(HostingAccount $account, Products_Service $newProduct, array $options = [])
    {
        $client = $this->getClientForControlPanel($account->control_panel);
        $newPrice = $this->pricingService->calculatePrice($newProduct, $options);
        $result = $client->changePackage($account->username, $newProduct->name);
        
        if ($result) {
            $account->package = $newProduct->name;
            $account->price = $newPrice;
            $account->save();
        }

        return $result;
    }

    protected function getClientForControlPanel($controlPanel)
    {
        switch ($controlPanel) {
            case 'cpanel':
                return $this->cpanelClient;
            case 'plesk':
                return $this->pleskClient;
            case 'directadmin':
                return $this->directAdminClient;
            default:
                throw new \Exception("Unsupported control panel: $controlPanel");
        }
    }
}