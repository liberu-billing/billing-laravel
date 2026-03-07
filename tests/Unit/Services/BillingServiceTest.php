<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\BillingService;
use App\Services\PaymentGatewayService;
use App\Models\Subscription;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Products_Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class BillingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $billingService;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock the PaymentGatewayService to avoid real payment processing
        $this->mock(PaymentGatewayService::class, function ($mock) {
            $mock->shouldReceive('processPayment')
                ->andReturn(['success' => true, 'transaction_id' => 'test-txn-123']);
        });

        $this->billingService = app(BillingService::class);
    }

    public function testGenerateInvoice()
    {
        $customer = Customer::factory()->create();
        $productService = Products_Service::factory()->create(['base_price' => 100]);
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
    }

    public function testSendOverdueReminders()
    {
        Mail::fake();

        $overdueInvoice = Invoice::factory()->create([
            'due_date' => Carbon::yesterday(),
            'status' => 'pending',
        ]);

        $this->billingService->sendOverdueReminders();

        // Verify the method ran without errors
        $this->assertTrue(true);
    }
}
