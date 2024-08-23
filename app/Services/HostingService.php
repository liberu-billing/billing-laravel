<?php

namespace App\Services;

use App\Models\HostingAccount;
use App\Services\ControlPanels\CpanelClient;
use App\Services\ControlPanels\PleskClient;
use App\Services\ControlPanels\DirectAdminClient;
use Illuminate\Support\Facades\Log;
use Exception;

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
        try {
            $client = $this->getClientForControlPanel($account->control_panel);
            $result = $client->createAccount($account->username, $account->domain, $account->package);
            
            if ($result) {
                $account->status = 'active';
                $account->save();
                Log::info("Account provisioned successfully", ['account_id' => $account->id]);
            } else {
                Log::error("Failed to provision account", ['account_id' => $account->id]);
            }

            return $result;
        } catch (Exception $e) {
            Log::error("Error provisioning account", [
                'account_id' => $account->id,
                'error' => $e->getMessage()
            ]);
            throw new Exception("Failed to provision account: " . $e->getMessage());
        }
    }

    public function suspendAccount(HostingAccount $account)
    {
        try {
            $client = $this->getClientForControlPanel($account->control_panel);
            $result = $client->suspendAccount($account->username);
            
            if ($result) {
                $account->status = 'suspended';
                $account->save();
                Log::info("Account suspended successfully", ['account_id' => $account->id]);
            } else {
                Log::error("Failed to suspend account", ['account_id' => $account->id]);
            }

            return $result;
        } catch (Exception $e) {
            Log::error("Error suspending account", [
                'account_id' => $account->id,
                'error' => $e->getMessage()
            ]);
            throw new Exception("Failed to suspend account: " . $e->getMessage());
        }
    }

    public function unsuspendAccount(HostingAccount $account)
    {
        try {
            $client = $this->getClientForControlPanel($account->control_panel);
            $result = $client->unsuspendAccount($account->username);
            
            if ($result) {
                $account->status = 'active';
                $account->save();
                Log::info("Account unsuspended successfully", ['account_id' => $account->id]);
            } else {
                Log::error("Failed to unsuspend account", ['account_id' => $account->id]);
            }

            return $result;
        } catch (Exception $e) {
            Log::error("Error unsuspending account", [
                'account_id' => $account->id,
                'error' => $e->getMessage()
            ]);
            throw new Exception("Failed to unsuspend account: " . $e->getMessage());
        }
    }

    public function upgradeAccount(HostingAccount $account, $newPackage)
    {
        try {
            $client = $this->getClientForControlPanel($account->control_panel);
            $result = $client->changePackage($account->username, $newPackage);
            
            if ($result) {
                $account->package = $newPackage;
                $account->save();
                Log::info("Account upgraded successfully", ['account_id' => $account->id, 'new_package' => $newPackage]);
            } else {
                Log::error("Failed to upgrade account", ['account_id' => $account->id, 'new_package' => $newPackage]);
            }

            return $result;
        } catch (Exception $e) {
            Log::error("Error upgrading account", [
                'account_id' => $account->id,
                'new_package' => $newPackage,
                'error' => $e->getMessage()
            ]);
            throw new Exception("Failed to upgrade account: " . $e->getMessage());
        }
    }

    public function terminateAccount(HostingAccount $account)
    {
        try {
            $client = $this->getClientForControlPanel($account->control_panel);
            $result = $client->terminateAccount($account->username);
            
            if ($result) {
                $account->status = 'terminated';
                $account->save();
                Log::info("Account terminated successfully", ['account_id' => $account->id]);
            } else {
                Log::error("Failed to terminate account", ['account_id' => $account->id]);
            }

            return $result;
        } catch (Exception $e) {
            Log::error("Error terminating account", [
                'account_id' => $account->id,
                'error' => $e->getMessage()
            ]);
            throw new Exception("Failed to terminate account: " . $e->getMessage());
        }
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
                throw new Exception("Unsupported control panel: $controlPanel");
        }
    }
}