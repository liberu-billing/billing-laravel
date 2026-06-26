<?php

namespace Tests\Unit\Services;

use App\Models\Customer;
use App\Models\Discount;
use App\Models\Invoice;
use App\Models\Invoice_Item;
use App\Models\Products_Service;
use App\Models\TaxExemption;
use App\Models\TaxRate;
use App\Models\Team;
use App\Services\BillingService;
use App\Services\CurrencyService;
use App\Services\TaxService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TaxCurrencyDiscountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // tax/currency caches key on team/country/currency; ids repeat across tests → flush.
        Cache::flush();
    }

    /**
     * Build an invoice with one item of the given total_price and product type,
     * scoped to a team (team_id is not fillable on Invoice, set directly).
     */
    private function invoiceWithItem(
        Team $team,
        Customer $customer,
        float $itemTotal,
        string $productType = 'service',
        string $currency = 'USD',
    ): Invoice {
        $invoice = Invoice::factory()->create([
            'customer_id' => $customer->id,
            'currency' => $currency,
        ]);
        $invoice->team_id = $team->id;
        $invoice->save();

        $product = Products_Service::factory()->create(['type' => $productType]);
        Invoice_Item::create([
            'invoice_id' => $invoice->id,
            'product_service_id' => $product->id,
            'quantity' => 1,
            'unit_price' => $itemTotal,
            'total_price' => $itemTotal,
            'currency' => $currency,
        ]);

        return $invoice->fresh();
    }

    // ---------------------------------------------------------------- Tax / VAT

    public function test_calculate_tax_applies_rate_to_item_total(): void
    {
        $team = Team::factory()->create();
        $customer = Customer::factory()->create(['country' => 'US', 'state' => 'CA']);
        $invoice = $this->invoiceWithItem($team, $customer, 100.00, 'service');

        TaxRate::create([
            'team_id' => $team->id,
            'name' => 'CA Sales Tax',
            'country' => 'US',
            'state' => 'CA',
            'service_type' => 'service',
            'rate' => 10.00,
            'is_active' => true,
        ]);

        $tax = app(TaxService::class)->calculateTax($invoice);

        // 100 * 10% = 10.00
        $this->assertEquals(10.00, (float) $tax);
    }

    public function test_calculate_tax_returns_zero_for_exempt_customer(): void
    {
        $team = Team::factory()->create();
        $customer = Customer::factory()->create(['country' => 'US', 'state' => 'CA']);
        $invoice = $this->invoiceWithItem($team, $customer, 100.00, 'service');

        TaxRate::create([
            'team_id' => $team->id,
            'name' => 'CA Sales Tax',
            'country' => 'US',
            'state' => 'CA',
            'service_type' => 'service',
            'rate' => 10.00,
            'is_active' => true,
        ]);

        TaxExemption::create([
            'customer_id' => $customer->id,
            'reason' => 'Non-profit',
            'is_active' => true,
            'expiry_date' => now()->addYear(),
        ]);

        $this->assertEquals(0, (float) app(TaxService::class)->calculateTax($invoice));
    }

    /**
     * BUG DOC: threshold tax is applied twice. applyThresholdRules() (TaxService.php:166-176)
     * already returns a *tax amount* (first 100 @ 10% + excess 100 @ 20% = 30), but
     * calculateItemTax() (TaxService.php:151-164) assigns that into $taxableAmount and then
     * multiplies by the rate AGAIN: 30 * 0.10 = 3.0. Correct result should be 30.00.
     */
    public function test_calculate_tax_applies_threshold_rules(): void
    {
        $team = Team::factory()->create();
        $customer = Customer::factory()->create(['country' => 'US', 'state' => 'CA']);
        $invoice = $this->invoiceWithItem($team, $customer, 200.00, 'service');

        TaxRate::create([
            'team_id' => $team->id,
            'name' => 'Tiered Tax',
            'country' => 'US',
            'state' => 'CA',
            'service_type' => 'service',
            'rate' => 10.00,
            'threshold_amount' => 100.00,
            'threshold_rate' => 20.00,
            'is_active' => true,
        ]);

        // Tiered: 100 @ 10% + 100 (excess) @ 20% = 30.00.
        $this->assertEquals(30.0, (float) app(TaxService::class)->calculateTax($invoice));
    }

    public function test_calculate_tax_skips_items_with_no_matching_rate(): void
    {
        $team = Team::factory()->create();
        $customer = Customer::factory()->create(['country' => 'US', 'state' => 'CA']);
        // product type 'product' but rate is for 'service' → no match
        $invoice = $this->invoiceWithItem($team, $customer, 100.00, 'product');

        TaxRate::create([
            'team_id' => $team->id,
            'name' => 'Service Tax',
            'country' => 'US',
            'state' => 'CA',
            'service_type' => 'service',
            'rate' => 10.00,
            'is_active' => true,
        ]);

        $this->assertEquals(0, (float) app(TaxService::class)->calculateTax($invoice));
    }

    // ---------------------------------------------------------------- Discounts

    public function test_apply_percentage_discount(): void
    {
        $team = Team::factory()->create();
        $customer = Customer::factory()->create();
        $invoice = $this->invoiceWithItem($team, $customer, 100.00, 'service', 'USD');

        Discount::create([
            'code' => 'PCT10',
            'name' => '10% off',
            'type' => 'percentage',
            'value' => 10.00,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'is_active' => true,
        ]);

        $result = app(BillingService::class)->applyDiscount($invoice, 'PCT10');

        $this->assertTrue($result['success']);
        $this->assertEquals(10.00, (float) $result['discount_amount']);

        $fresh = $invoice->fresh();
        $this->assertEquals(10.00, (float) $fresh->discount_amount);
        $this->assertEquals(90.00, (float) $fresh->total_amount); // subtotal 100 - 10
    }

    public function test_apply_fixed_discount_same_currency(): void
    {
        $team = Team::factory()->create();
        $customer = Customer::factory()->create();
        $invoice = $this->invoiceWithItem($team, $customer, 100.00, 'service', 'USD');

        Discount::create([
            'code' => 'FIX25',
            'name' => '25 off',
            'type' => 'fixed',
            'value' => 25.00,
            'currency' => 'USD',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'is_active' => true,
        ]);

        $result = app(BillingService::class)->applyDiscount($invoice, 'FIX25');

        $this->assertTrue($result['success']);
        $this->assertEquals(25.00, (float) $result['discount_amount']);
        $this->assertEquals(75.00, (float) $invoice->fresh()->total_amount);
    }

    public function test_apply_invalid_discount_code_fails(): void
    {
        $team = Team::factory()->create();
        $customer = Customer::factory()->create();
        $invoice = $this->invoiceWithItem($team, $customer, 100.00, 'service', 'USD');

        $result = app(BillingService::class)->applyDiscount($invoice, 'NOPE');

        $this->assertFalse($result['success']);
    }

    public function test_apply_expired_discount_fails(): void
    {
        $team = Team::factory()->create();
        $customer = Customer::factory()->create();
        $invoice = $this->invoiceWithItem($team, $customer, 100.00, 'service', 'USD');

        Discount::create([
            'code' => 'OLD',
            'name' => 'expired',
            'type' => 'percentage',
            'value' => 10.00,
            'start_date' => now()->subDays(10),
            'end_date' => now()->subDay(),
            'is_active' => true,
        ]);

        $result = app(BillingService::class)->applyDiscount($invoice, 'OLD');

        $this->assertFalse($result['success']);
    }

    public function test_apply_discount_increments_used_count(): void
    {
        $team = Team::factory()->create();
        $customer = Customer::factory()->create();
        $invoice = $this->invoiceWithItem($team, $customer, 100.00, 'service', 'USD');

        $discount = Discount::create([
            'code' => 'USE1',
            'name' => 'usable',
            'type' => 'percentage',
            'value' => 10.00,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'is_active' => true,
            'used_count' => 0,
        ]);

        app(BillingService::class)->applyDiscount($invoice, 'USE1');

        $this->assertEquals(1, $discount->fresh()->used_count);
    }

    /**
     * BUG DOC: applyDiscount overwrites total_amount with (subtotal - discount),
     * ignoring tax_amount. The invoice's own final_total accessor includes tax,
     * so the two diverge after a discount is applied.
     */
    public function test_apply_discount_keeps_tax_in_total_amount(): void
    {
        $team = Team::factory()->create();
        $customer = Customer::factory()->create();
        $invoice = $this->invoiceWithItem($team, $customer, 100.00, 'service', 'USD');
        $invoice->tax_amount = 10.00;
        $invoice->save();

        Discount::create([
            'code' => 'PCT10B',
            'name' => '10% off',
            'type' => 'percentage',
            'value' => 10.00,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'is_active' => true,
        ]);

        app(BillingService::class)->applyDiscount($invoice, 'PCT10B');
        $fresh = $invoice->fresh();

        // total_amount = subtotal(100) + tax(10) - discount(10) = 100, tax retained
        $this->assertEquals(100.00, (float) $fresh->total_amount);
        // matches the final_total accessor = subtotal + tax - discount
        $this->assertEquals((float) $fresh->final_total, (float) $fresh->total_amount);
    }

    // ---------------------------------------------------------------- Currency

    public function test_billing_convert_currency_same_currency_returns_amount(): void
    {
        $this->assertEquals(100.00, app(BillingService::class)->convertCurrency(100, 'USD', 'USD'));
    }

    /**
     * BUG DOC: convertCurrency hardcodes fromRate=1, toRate=1, so it returns the
     * input amount unchanged for ANY currency pair — no real conversion happens.
     */
    public function test_billing_convert_currency_is_hardcoded_one_to_one(): void
    {
        // USD→EUR should change the amount, but the stub returns it unchanged.
        $this->assertEquals(100.00, app(BillingService::class)->convertCurrency(100, 'USD', 'EUR'));
    }

    public function test_currency_service_uses_cached_rate(): void
    {
        Cache::put('currency_rate_USD_EUR', 0.5, 3600);

        $this->assertEquals(50.0, app(CurrencyService::class)->convert(100.0, 'USD', 'EUR'));
    }

    /**
     * BUG DOC: CurrencyService::calculateRate only assigns $rate inside the cache-hit
     * branch. On a cache miss it falls through to `return $amount * $rate` with $rate
     * still null, so convert() returns 0.0 instead of a real/converted amount.
     * (CurrencyService.php:62-68 — rate calculation logic is never implemented.)
     */
    public function test_currency_service_returns_zero_on_cache_miss(): void
    {
        $this->assertEquals(0.0, app(CurrencyService::class)->convert(100.0, 'USD', 'GBP'));
    }
}
