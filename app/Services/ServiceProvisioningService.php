<?php

namespace App\Services;

use App\Models\Products_Service;
use App\Models\Subscription;
use App\Models\HostingAccount;
use Exception;

class ServiceProvisioningService
{
    protected $hostingService;

    public function __construct(HostingService $hostingService)
    {
        $this->hostingService = $hostingService;
    }

    public function provisionService(Subscription $subscription)
    {
        $service = $subscription->productService;

        switch ($service->type) {
            case 'hosting':
                return $this->provisionHosting($subscription);
            case 'domain':
                return $this->provisionDomain($subscription);
            case 'email':
                return $this->provisionEmail($subscription);
            default:
                throw new Exception('Unsupported service type');
        }
    }

    private function provisionHosting(Subscription $subscription)
    {
        $service = $subscription->productService;
        
        // Create hosting account record
        $hostingAccount = new HostingAccount([
            'customer_id' => $subscription->customer_id,
            'subscription_id' => $subscription->id,
            'username' => $this->generateUsername($subscription->customer),
            'domain' => $subscription->domain ?? '',
            'package' => $service->name,
            'status' => 'pending',
        ]);
        
        $hostingAccount->save();

        // Provision the account on the control panel
        try {
            $this->hostingService->provisionAccount($hostingAccount, $service);
            return $hostingAccount;
        } catch (Exception $e) {
            $hostingAccount->status = 'failed';
            $hostingAccount->save();
            throw $e;
        }
    }

    private function provisionDomain(Subscription $subscription)
    {
        // Implement domain provisioning logic
        // This would typically involve interacting with a domain registrar API
        // For now, we'll just return a success message
        return ['status' => 'success', 'message' => 'Domain provisioned successfully'];
    }

    private function provisionEmail(Subscription $subscription)
    {
        // Implement email provisioning logic
        // This would typically involve creating email accounts on your mail server
        // For now, we'll just return a success message
        return ['status' => 'success', 'message' => 'Email service provisioned successfully'];
    }

    private function generateUsername($customer)
    {
        // Implement logic to generate a unique username
        $baseName = strtolower(preg_replace('/[^a-z0-9]/i', '', $customer->name));
        $baseName = substr($baseName, 0, 8);
        return $baseName . rand(100, 999);
    }

    public function manageService(Subscription $subscription, $action, $options = [])
    {
        $service = $subscription->productService;

        switch ($service->type) {
            case 'hosting':
                return $this->manageHosting($subscription, $action, $options);
            case 'domain':
                return $this->manageDomain($subscription, $action);
            case 'email':
                return $this->manageEmail($subscription, $action);
            default:
                throw new Exception('Unsupported service type');
        }
    }

    private function manageHosting(Subscription $subscription, $action, $options = [])
    {
        $hostingAccount = HostingAccount::where('subscription_id', $subscription->id)->first();

        if (!$hostingAccount) {
            throw new Exception('Hosting account not found');
        }

        switch ($action) {
            case 'suspend':
                return $this->hostingService->suspendAccount($hostingAccount);
            case 'unsuspend':
                return $this->hostingService->unsuspendAccount($hostingAccount);
            case 'terminate':
                return $this->hostingService->terminateAccount($hostingAccount);
            case 'upgrade':
                if (!isset($options['new_product'])) {
                    throw new Exception('New product required for upgrade');
                }
                return $this->hostingService->upgradeAccount($hostingAccount, $options['new_product'], $options);
            case 'downgrade':
                if (!isset($options['new_product'])) {
                    throw new Exception('New product required for downgrade');
                }
                return $this->hostingService->downgradeAccount($hostingAccount, $options['new_product'], $options);
            case 'add_addon':
                if (!isset($options['addon'])) {
                    throw new Exception('Addon name required');
                }
                return $this->hostingService->addAddon($hostingAccount, $options['addon']);
            case 'remove_addon':
                if (!isset($options['addon'])) {
                    throw new Exception('Addon name required');
                }
                return $this->hostingService->removeAddon($hostingAccount, $options['addon']);
            default:
                throw new Exception('Unsupported action for hosting account');
        }
    }

    private function manageDomain(Subscription $subscription, $action)
    {
        // Implement domain management logic
        // This would typically involve interacting with a domain registrar API
        // For now, we'll just return a success message
        return ['status' => 'success', 'message' => "Domain {$action} successfully"];
    }

    private function manageEmail(Subscription $subscription, $action)
    {
        // Implement email management logic
        // This would typically involve managing email accounts on your mail server
        // For now, we'll just return a success message
        return ['status' => 'success', 'message' => "Email service {$action} successfully"];
    }
}
