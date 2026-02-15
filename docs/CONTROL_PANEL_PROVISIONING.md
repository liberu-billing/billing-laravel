# Control Panel Auto-Provisioning

This document describes the auto-provisioning system for hosting accounts across multiple control panel platforms.

## Supported Control Panels

The billing system supports automated provisioning, management, and termination of hosting accounts on the following control panels:

1. **cPanel/WHM** - Full support for account creation, suspension, unsuspension, termination, package changes, and addon management
2. **Plesk** - Complete management via XML API including account lifecycle and addons
3. **DirectAdmin** - Full provisioning and management capabilities via DirectAdmin API
4. **Virtualmin (GPL/Pro)** - Support for both GPL and Pro versions with complete account management
5. **Liberu Control Panel** - Native integration with [liberu-control-panel-laravel](https://github.com/liberu-control-panel/control-panel-laravel) via REST API

## Features

### Account Lifecycle Management

- **Provisioning**: Automatically create hosting accounts when subscriptions are activated
- **Suspension**: Suspend accounts for non-payment or policy violations
- **Unsuspension**: Restore suspended accounts when issues are resolved
- **Termination**: Permanently delete hosting accounts when subscriptions end

### Package Management

- **Upgrade**: Move accounts to higher-tier packages with more resources
- **Downgrade**: Move accounts to lower-tier packages
- **Package Changes**: Seamlessly transition between hosting plans

### Addon Management

- **Add Addons**: Enable additional features like SSL certificates, backups, etc.
- **Remove Addons**: Disable addons when no longer needed
- **Track Addons**: Maintain a list of active addons for each account

## Architecture

### Service Classes

#### HostingService
Main service for provisioning and managing hosting accounts across all control panels.

```php
use App\Services\HostingService;

$hostingService = app(HostingService::class);

// Provision a new account
$hostingService->provisionAccount($hostingAccount, $product);

// Suspend an account
$hostingService->suspendAccount($hostingAccount);

// Unsuspend an account
$hostingService->unsuspendAccount($hostingAccount);

// Upgrade to a new package
$hostingService->upgradeAccount($hostingAccount, $newProduct);

// Downgrade to a new package
$hostingService->downgradeAccount($hostingAccount, $newProduct);

// Terminate an account
$hostingService->terminateAccount($hostingAccount);

// Add an addon
$hostingService->addAddon($hostingAccount, 'ssl-certificate');

// Remove an addon
$hostingService->removeAddon($hostingAccount, 'ssl-certificate');
```

#### ServiceProvisioningService
High-level service for managing all types of services (hosting, domains, email).

```php
use App\Services\ServiceProvisioningService;

$provisioningService = app(ServiceProvisioningService::class);

// Provision any type of service
$provisioningService->provisionService($subscription);

// Manage hosting services
$provisioningService->manageService($subscription, 'suspend');
$provisioningService->manageService($subscription, 'unsuspend');
$provisioningService->manageService($subscription, 'terminate');
$provisioningService->manageService($subscription, 'upgrade', [
    'new_product' => $newProduct
]);
$provisioningService->manageService($subscription, 'add_addon', [
    'addon' => 'ssl-certificate'
]);
```

### Control Panel Clients

Each control panel has a dedicated client class that handles API communication:

- `CpanelClient` - WHM JSON API
- `PleskClient` - Plesk XML API
- `DirectAdminClient` - DirectAdmin API
- `VirtualminClient` - Virtualmin remote API
- `LiberuControlPanelClient` - Liberu Control Panel REST API

All clients implement the same interface for consistency:
- `createAccount($username, $domain, $package)`
- `suspendAccount($username)`
- `unsuspendAccount($username)`
- `changePackage($username, $newPackage)`
- `terminateAccount($username)`
- `addAddon($username, $addon)`
- `removeAddon($username, $addon)`

## Database Schema

### hosting_servers

Stores control panel server configurations:

```php
Schema::create('hosting_servers', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('hostname');
    $table->string('username')->nullable();
    $table->string('ip_address')->nullable();
    $table->enum('control_panel', ['cpanel', 'plesk', 'directadmin', 'virtualmin', 'virtualmin-gpl', 'virtualmin-pro', 'liberu']);
    $table->string('api_token');
    $table->string('api_url');
    $table->boolean('is_active')->default(true);
    $table->integer('max_accounts')->default(0);
    $table->integer('active_accounts')->default(0);
    $table->timestamps();
});
```

### hosting_accounts

Stores provisioned hosting account details:

```php
Schema::create('hosting_accounts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('customer_id')->constrained()->onDelete('cascade');
    $table->foreignId('subscription_id')->constrained()->onDelete('cascade');
    $table->foreignId('hosting_server_id')->nullable()->constrained()->onDelete('set null');
    $table->string('control_panel');
    $table->string('username');
    $table->string('domain');
    $table->string('package');
    $table->string('status');
    $table->decimal('price', 10, 2)->nullable();
    $table->json('addons')->nullable();
    $table->timestamps();
});
```

## Configuration

### Adding a Hosting Server

Create a hosting server record to configure a control panel:

```php
use App\Models\HostingServer;

HostingServer::create([
    'name' => 'cPanel Server 1',
    'hostname' => 'server1.example.com',
    'username' => 'root',
    'ip_address' => '192.168.1.100',
    'control_panel' => 'cpanel',
    'api_token' => 'your-api-token-here',
    'api_url' => 'https://server1.example.com:2087',
    'is_active' => true,
    'max_accounts' => 500,
    'active_accounts' => 0,
]);
```

### Control Panel Specific Configuration

#### cPanel/WHM
- **Port**: 2087 (WHM)
- **API Type**: WHM JSON API
- **Authentication**: WHM API token
- **API URL Format**: `https://hostname:2087`

#### Plesk
- **Port**: 8443
- **API Type**: XML API
- **Authentication**: API Key
- **API URL Format**: `https://hostname:8443`

#### DirectAdmin
- **Port**: 2222
- **API Type**: DirectAdmin API
- **Authentication**: Login Key (base64)
- **API URL Format**: `https://hostname:2222`

#### Virtualmin
- **Port**: 10000
- **API Type**: Remote API
- **Authentication**: Basic Auth (username:password)
- **API URL Format**: `https://hostname:10000`

#### Liberu Control Panel
- **Port**: Custom (usually 443)
- **API Type**: REST API
- **Authentication**: Bearer token
- **API URL Format**: Configurable (e.g., `https://control-panel.example.com`)

## Server Selection

When provisioning a new account, the system automatically selects an appropriate server based on:

1. Server is active (`is_active = true`)
2. Server is not at capacity (`active_accounts < max_accounts`)
3. Server with the lowest number of active accounts is selected

You can also specify a specific server when provisioning by setting the `hosting_server_id` on the product/service configuration.

## Error Handling

All control panel clients log errors and return `false` on failure. The system logs:

- Successful API calls (INFO level)
- Failed API calls (ERROR level)
- API communication errors (ERROR level)

Check `storage/logs/laravel.log` for detailed error messages.

## Testing

Comprehensive tests are included for all control panel operations:

```bash
php artisan test --filter=HostingServiceTest
php artisan test --filter=ServiceProvisioningServiceTest
php artisan test --filter=ControlPanels
```

### Test Coverage

- ✅ Account provisioning for all control panels
- ✅ Account suspension and unsuspension
- ✅ Account termination
- ✅ Package upgrades and downgrades
- ✅ Addon management (add/remove)
- ✅ Server selection logic
- ✅ Error handling

## Security Considerations

1. **API Tokens**: Store API tokens securely and never commit them to version control
2. **SSL Verification**: In production, enable SSL verification for API calls
3. **Access Control**: Limit who can modify hosting server configurations
4. **Audit Logging**: All provisioning actions are logged for audit purposes

## Examples

### Complete Provisioning Flow

```php
use App\Models\Customer;
use App\Models\Products_Service;
use App\Models\Subscription;
use App\Services\ServiceProvisioningService;

// Create a subscription
$customer = Customer::find(1);
$product = Products_Service::where('type', 'hosting')->first();

$subscription = Subscription::create([
    'customer_id' => $customer->id,
    'product_service_id' => $product->id,
    'domain' => 'customer-site.com',
    'status' => 'active',
    'start_date' => now(),
    'end_date' => now()->addMonth(),
]);

// Provision the hosting account
$provisioningService = app(ServiceProvisioningService::class);
$hostingAccount = $provisioningService->provisionService($subscription);

// Later: Upgrade the account
$premiumProduct = Products_Service::where('name', 'premium-plan')->first();
$provisioningService->manageService($subscription, 'upgrade', [
    'new_product' => $premiumProduct
]);

// Add SSL certificate addon
$provisioningService->manageService($subscription, 'add_addon', [
    'addon' => 'ssl-certificate'
]);

// Suspend for non-payment
$provisioningService->manageService($subscription, 'suspend');

// Unsuspend after payment received
$provisioningService->manageService($subscription, 'unsuspend');

// Terminate when subscription ends
$provisioningService->manageService($subscription, 'terminate');
```

## Future Enhancements

Potential improvements for the provisioning system:

- [ ] Support for additional control panels (ISPConfig, CyberPanel, etc.)
- [ ] Webhook support for real-time status updates
- [ ] Automated DNS management integration
- [ ] Resource usage monitoring and alerts
- [ ] Automated backups before package changes
- [ ] Queue-based provisioning for better reliability
- [ ] Multi-server load balancing strategies
- [ ] Automated failover when servers are unavailable
