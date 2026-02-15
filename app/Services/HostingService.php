<?php

namespace App\Services;

use Exception;
use App\Models\HostingAccount;
use App\Models\Products_Service;
use App\Models\HostingServer;
use App\Services\ControlPanels\CpanelClient;
use App\Services\ControlPanels\PleskClient;
use App\Services\ControlPanels\DirectAdminClient;
use App\Services\ControlPanels\VirtualminClient;
use App\Services\ControlPanels\LiberuControlPanelClient;
use App\Services\PricingService;
use Illuminate\Support\Facades\Log;

class HostingService
{
    protected $cpanelClient;
    protected $pleskClient;
    protected $directAdminClient;
    protected $virtualminClient;
    protected $liberuControlPanelClient;
    protected $pricingService;

    public function __construct(
        CpanelClient $cpanelClient,
        PleskClient $pleskClient,
        DirectAdminClient $directAdminClient,
        VirtualminClient $virtualminClient,
        LiberuControlPanelClient $liberuControlPanelClient,
        PricingService $pricingService
    ) {
        $this->cpanelClient = $cpanelClient;
        $this->pleskClient = $pleskClient;
        $this->directAdminClient = $directAdminClient;
        $this->virtualminClient = $virtualminClient;
        $this->liberuControlPanelClient = $liberuControlPanelClient;
        $this->pricingService = $pricingService;
    }

    public function provisionAccount(HostingAccount $account, Products_Service $product, array $options = [])
    {
        // Select least loaded server of the specified type
        $server = $this->selectServer($product->hosting_server_id);
        
        if (!$server) {
            throw new Exception('No available servers found');
        }

        $client = $this->getClientForControlPanel($server->control_panel);
        $price = $this->pricingService->calculatePrice($product, $options);
        
        // Configure client with server details
        $client->setServer($server);
        
        $result = $client->createAccount($account->username, $account->domain, $account->package);
        
        if ($result) {
            $account->status = 'active';
            $account->price = $price;
            $account->hosting_server_id = $server->id;
            $account->save();
            
            // Increment server account count
            $server->increment('active_accounts');
            
            Log::info("Provisioned new hosting account", [
                'account_id' => $account->id,
                'server_id' => $server->id
            ]);
        }

        return $result;
    }

    public function suspendAccount(HostingAccount $account)
    {
        $server = HostingServer::findOrFail($account->hosting_server_id);
        $client = $this->getClientForControlPanel($server->control_panel);
        $client->setServer($server);
        
        $result = $client->suspendAccount($account->username);
        
        if ($result) {
            $account->status = 'suspended';
            $account->save();
            
            Log::info("Suspended hosting account", ['account_id' => $account->id]);
        }

        return $result;
    }

    public function unsuspendAccount(HostingAccount $account)
    {
        $server = HostingServer::findOrFail($account->hosting_server_id);
        $client = $this->getClientForControlPanel($server->control_panel);
        $client->setServer($server);
        
        $result = $client->unsuspendAccount($account->username);
        
        if ($result) {
            $account->status = 'active';
            $account->save();
            
            Log::info("Unsuspended hosting account", ['account_id' => $account->id]);
        }

        return $result;
    }

    public function upgradeAccount(HostingAccount $account, Products_Service $newProduct, array $options = [])
    {
        $server = HostingServer::findOrFail($account->hosting_server_id);
        $client = $this->getClientForControlPanel($server->control_panel);
        $client->setServer($server);
        
        $newPrice = $this->pricingService->calculatePrice($newProduct, $options);
        $result = $client->changePackage($account->username, $newProduct->name);
        
        if ($result) {
            $account->package = $newProduct->name;
            $account->price = $newPrice;
            $account->save();
            
            Log::info("Upgraded hosting account", [
                'account_id' => $account->id,
                'new_package' => $newProduct->name
            ]);
        }

        return $result;
    }

    public function downgradeAccount(HostingAccount $account, Products_Service $newProduct, array $options = [])
    {
        $server = HostingServer::findOrFail($account->hosting_server_id);
        $client = $this->getClientForControlPanel($server->control_panel);
        $client->setServer($server);
        
        $newPrice = $this->pricingService->calculatePrice($newProduct, $options);
        $result = $client->changePackage($account->username, $newProduct->name);
        
        if ($result) {
            $account->package = $newProduct->name;
            $account->price = $newPrice;
            $account->save();
            
            Log::info("Downgraded hosting account", [
                'account_id' => $account->id,
                'new_package' => $newProduct->name
            ]);
        }

        return $result;
    }

    public function terminateAccount(HostingAccount $account)
    {
        $server = HostingServer::findOrFail($account->hosting_server_id);
        $client = $this->getClientForControlPanel($server->control_panel);
        $client->setServer($server);
        
        $result = $client->terminateAccount($account->username);
        
        if ($result) {
            $account->status = 'terminated';
            $account->save();
            
            // Decrement server account count
            $server->decrement('active_accounts');
            
            Log::info("Terminated hosting account", ['account_id' => $account->id]);
        }

        return $result;
    }

    public function addAddon(HostingAccount $account, string $addon)
    {
        $server = HostingServer::findOrFail($account->hosting_server_id);
        $client = $this->getClientForControlPanel($server->control_panel);
        $client->setServer($server);
        
        $result = $client->addAddon($account->username, $addon);
        
        if ($result) {
            $addons = json_decode($account->addons ?? '[]', true);
            if (!in_array($addon, $addons)) {
                $addons[] = $addon;
                $account->addons = json_encode($addons);
                $account->save();
            }
            
            Log::info("Added addon to hosting account", [
                'account_id' => $account->id,
                'addon' => $addon
            ]);
        }

        return $result;
    }

    public function removeAddon(HostingAccount $account, string $addon)
    {
        $server = HostingServer::findOrFail($account->hosting_server_id);
        $client = $this->getClientForControlPanel($server->control_panel);
        $client->setServer($server);
        
        $result = $client->removeAddon($account->username, $addon);
        
        if ($result) {
            $addons = json_decode($account->addons ?? '[]', true);
            $addons = array_filter($addons, fn($a) => $a !== $addon);
            $account->addons = json_encode(array_values($addons));
            $account->save();
            
            Log::info("Removed addon from hosting account", [
                'account_id' => $account->id,
                'addon' => $addon
            ]);
        }

        return $result;
    }

    protected function selectServer($serverId = null)
    {
        if ($serverId) {
            return HostingServer::find($serverId);
        }

        return HostingServer::where('is_active', true)
            ->whereRaw('active_accounts < max_accounts')
            ->orderBy('active_accounts')
            ->first();
    }

    protected function getClientForControlPanel($controlPanel)
    {
        return match ($controlPanel) {
            'cpanel' => $this->cpanelClient,
            'plesk' => $this->pleskClient,
            'directadmin' => $this->directAdminClient,
            'virtualmin' => $this->virtualminClient,
            'virtualmin-gpl' => $this->virtualminClient,
            'virtualmin-pro' => $this->virtualminClient,
            'liberu' => $this->liberuControlPanelClient,
            default => throw new Exception("Unsupported control panel: $controlPanel"),
        };
    }
}