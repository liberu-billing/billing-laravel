<?php

namespace App\Services;

use App\Models\HostingAccount;
use App\Services\ControlPanels\CpanelClient;
use App\Services\ControlPanels\PleskClient;
use App\Services\ControlPanels\DirectAdminClient;

class HostingService
{
    protected $cpanelClient;
    protected $pleskClient;
    protected $directAdminClient;

    public function __construct(
        CpanelClient $cpanelClient,
        PleskClient $pleskClient,
        DirectAdminClient $directAdminClient
    ) {
        $this->cpanelClient = $cpanelClient;
        $this->pleskClient = $pleskClient;
        $this->directAdminClient = $directAdminClient;
    }

    public function provisionAccount(HostingAccount $account)
    {
        $client = $this->getClientForControlPanel($account->control_panel);
        $result = $client->createAccount($account->username, $account->domain, $account->package);
        
        if ($result) {
            $account->status = 'active';
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

    public function upgradeAccount(HostingAccount $account, $newPackage)
    {
        $client = $this->getClientForControlPanel($account->control_panel);
        $result = $client->changePackage($account->username, $newPackage);
        
        if ($result) {
            $account->package = $newPackage;
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