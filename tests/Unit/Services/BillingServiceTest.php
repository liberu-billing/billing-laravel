<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\BillingService;
use App\Models\Subscription;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Products_Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class BillingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $billingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->billingService = new BillingService();
    }

    public function testGenerateInvoice()
    {
        $customer = Customer::factory()->create();
        $productService = Products_Service::factory()->create(['price' => 100]);
        $subscription = Subscription::factory()->create([
            'customer_id' => $customer->id,
            'product_service_id' => $productService->id,
        ]);

        $invoice = $this->billingService->generateInvoice($subscription);

        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertEquals($customer->id, $invoice->customer_id);
        $this->assertEquals(100, $invoice->total_amount);
        $this->assertEquals('pending', $invoice->status);
    }

    public function testProcessRecurringBilling()
    {
        $subscription = Subscription::factory()->create([
            'end_date' => Carbon::yesterday(),
            'status' => 'active',
        ]);

        $this->billingService->processRecurringBilling();

        $this->assertDatabaseHas('invoices', [
            'customer_id' => $subscription->customer_id,
        ]);

        $updatedSubscription = $subscription->fresh();
        $this->assertTrue($updatedSubscription->end_date->isAfter(Carbon::yesterday()));
    }

    public function testSendOverdueReminders()
    {
        $overdueInvoice = Invoice::factory()->create([
            'due_date' => Carbon::yesterday(),
            'status' => 'pending',
        ]);

        $this->billingService->sendOverdueReminders();

        // Assert that the reminder was sent (you might need to mock the Mail facade)
        // $this->assertTrue(Mail::has(OverdueInvoiceReminder::class));
    }
}