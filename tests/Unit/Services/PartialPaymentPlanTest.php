<?php

namespace Tests\Unit\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\PaymentPlan;
use App\Services\BillingService;
use App\Services\PartialPaymentService;
use App\Services\PaymentGatewayService;
use App\Services\PaymentPlanService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartialPaymentPlanTest extends TestCase
{
    use RefreshDatabase;

    private function gateway(): PaymentGateway
    {
        return PaymentGateway::create([
            'name' => 'Test Gateway',
            'api_key' => 'key',
            'secret_key' => 'secret',
        ]);
    }

    /**
     * Build a PartialPaymentService whose gateway always reports success,
     * so the deterministic record-keeping logic can be exercised in isolation.
     */
    private function partialPaymentServiceWithSuccessfulGateway(): PartialPaymentService
    {
        $this->mock(PaymentGatewayService::class, function ($mock): void {
            $mock->shouldReceive('processPayment')
                ->andReturn(['success' => true, 'transaction_id' => 'txn-ppp-1']);
        });

        return new PartialPaymentService(app(PaymentGatewayService::class));
    }

    // ---------------------------------------------------------------------
    // Partial payments
    // ---------------------------------------------------------------------

    /**
     * BUG: processPartialPayment can never record a payment. It builds the
     * Payment WITHOUT payment_method (PartialPaymentService:23-31), but
     * payments.payment_method is a NOT NULL enum (create_payments_table:20),
     * so payment->save() (line 37) throws and the DB transaction rolls back.
     * On top of that, even if the save succeeded, a non-full payment would
     * then fail because updateInvoiceStatus sets status='partially_paid',
     * which is not in the invoices.status enum('pending','paid','overdue')
     * (create_invoices_table:23 / PartialPaymentService:68). Net effect: the
     * method always returns success=false and persists nothing.
     */
    public function test_partial_payment_never_persists_due_to_missing_payment_method(): void
    {
        $gateway = $this->gateway();
        $invoice = Invoice::factory()->create([
            'total_amount' => 100.00,
            'status' => 'pending',
        ]);

        $result = $this->partialPaymentServiceWithSuccessfulGateway()
            ->processPartialPayment($invoice, 40.00, $gateway->id);

        $this->assertFalse($result['success']);
        // Transaction rolled back: no payment persisted, invoice untouched.
        $this->assertDatabaseCount('payments', 0);
        $this->assertEquals('pending', $invoice->fresh()->status);
    }

    public function test_remaining_amount_reflects_recorded_payments(): void
    {
        $gateway = $this->gateway();
        $invoice = Invoice::factory()->create([
            'total_amount' => 100.00,
            'status' => 'pending',
        ]);

        $this->assertEquals(100.00, (float) $invoice->remaining_amount);

        Payment::create([
            'invoice_id' => $invoice->id,
            'payment_gateway_id' => $gateway->id,
            'amount' => 30.00,
            'currency' => $invoice->currency,
            'payment_method' => 'credit card',
            'transaction_id' => 'txn-a',
            'payment_date' => now(),
        ]);
        $this->assertEquals(70.00, (float) $invoice->fresh()->remaining_amount);

        Payment::create([
            'invoice_id' => $invoice->id,
            'payment_gateway_id' => $gateway->id,
            'amount' => 25.00,
            'currency' => $invoice->currency,
            'payment_method' => 'credit card',
            'transaction_id' => 'txn-b',
            'payment_date' => now(),
        ]);
        $this->assertEquals(45.00, (float) $invoice->fresh()->remaining_amount);
    }

    /**
     * BUG: the amount guard (PartialPaymentService:16-18) sits BEFORE
     * DB::beginTransaction()/try (lines 20-22), so an invalid amount throws an
     * uncaught Exception instead of returning the ['success'=>false] failure
     * array the catch block produces for every other error.
     */
    public function test_partial_payment_exceeding_remaining_throws_instead_of_returning_failure(): void
    {
        $gateway = $this->gateway();
        $invoice = Invoice::factory()->create([
            'total_amount' => 100.00,
            'status' => 'pending',
        ]);

        $service = new PartialPaymentService(app(PaymentGatewayService::class));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid partial payment amount');
        $service->processPartialPayment($invoice, 150.00, $gateway->id);
    }

    public function test_partial_payment_of_zero_throws(): void
    {
        $gateway = $this->gateway();
        $invoice = Invoice::factory()->create([
            'total_amount' => 100.00,
            'status' => 'pending',
        ]);

        $service = new PartialPaymentService(app(PaymentGatewayService::class));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid partial payment amount');
        $service->processPartialPayment($invoice, 0.0, $gateway->id);
    }

    /**
     * BillingService::handlePartialPayment news-up `new PaymentGatewayService`
     * internally (BillingService:936), bypassing the container — so the gateway
     * cannot be mocked. Combined with the fact that processPartialPayment never
     * sets payment_method, the real gateway rejects the payment up front
     * (PaymentGatewayService:49), so this entry point can only ever fail in a
     * unit test. Documents both the testability gap and the deterministic fail.
     */
    public function test_handle_partial_payment_cannot_be_unit_tested_and_fails(): void
    {
        $gateway = $this->gateway();
        $invoice = Invoice::factory()->create([
            'total_amount' => 100.00,
            'status' => 'pending',
        ]);

        $result = app(BillingService::class)->handlePartialPayment($invoice, 40.00, $gateway->id);

        $this->assertFalse($result['success']);
    }

    // ---------------------------------------------------------------------
    // Payment plans (BillingService::setupPaymentPlan -> Invoice::createPaymentPlan)
    // ---------------------------------------------------------------------

    public function test_setup_payment_plan_splits_invoice_into_equal_installments(): void
    {
        Carbon::setTestNow('2026-06-26');

        $invoice = Invoice::factory()->create([
            'total_amount' => 120.00,
            'is_installment' => false,
        ]);

        $billingService = app(BillingService::class);
        $plan = $billingService->setupPaymentPlan($invoice, 3, 'monthly');

        $this->assertInstanceOf(PaymentPlan::class, $plan);
        $this->assertEquals(3, $plan->total_installments);
        $this->assertEquals(40.00, (float) $plan->installment_amount);
        $this->assertEquals('monthly', $plan->frequency);
        $this->assertEquals('active', $plan->status);
        // next_due_date one month after the start date (now).
        $this->assertEquals('2026-07-26', $plan->next_due_date->format('Y-m-d'));

        Carbon::setTestNow();
    }

    public function test_setup_payment_plan_rounds_installment_amount(): void
    {
        $invoice = Invoice::factory()->create([
            'total_amount' => 100.00,
            'is_installment' => false,
        ]);

        $plan = app(BillingService::class)->setupPaymentPlan($invoice, 3, 'monthly');

        // round(100/3, 2) = 33.33; note 33.33 * 3 = 99.99 (rounding shortfall).
        $this->assertEquals(33.33, (float) $plan->installment_amount);
    }

    public function test_setup_payment_plan_twice_throws(): void
    {
        $invoice = Invoice::factory()->create([
            'total_amount' => 90.00,
            'is_installment' => false,
        ]);

        $billingService = app(BillingService::class);
        $billingService->setupPaymentPlan($invoice, 3, 'monthly');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invoice already has a payment plan');
        $billingService->setupPaymentPlan($invoice->fresh(), 3, 'monthly');
    }

    public function test_cannot_create_payment_plan_on_installment_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'total_amount' => 90.00,
            'is_installment' => true,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot create payment plan for an installment invoice');
        app(BillingService::class)->setupPaymentPlan($invoice, 3, 'monthly');
    }

    // ---------------------------------------------------------------------
    // PaymentPlanService::createInstallmentInvoice / processPaymentPlans
    // ---------------------------------------------------------------------

    public function test_create_installment_invoice_generates_child_invoice_and_advances_due_date(): void
    {
        Carbon::setTestNow('2026-06-26');

        $invoice = Invoice::factory()->create([
            'total_amount' => 120.00,
            'invoice_number' => 'INV-PLAN',
            'is_installment' => false,
        ]);
        $plan = app(BillingService::class)->setupPaymentPlan($invoice, 3, 'monthly');

        $originalNextDue = $plan->next_due_date->copy();

        $installment = app(PaymentPlanService::class)->createInstallmentInvoice($plan->fresh());

        $this->assertTrue((bool) $installment->is_installment);
        $this->assertEquals($invoice->id, $installment->parent_invoice_id);
        $this->assertEquals(40.00, (float) $installment->total_amount);
        $this->assertEquals('pending', $installment->status);
        $this->assertEquals('INV-PLAN-INST1', $installment->invoice_number);

        // Child invoice is discoverable via the parent invoice relationship.
        $this->assertEquals(1, $invoice->fresh()->installments()->count());

        // The plan's next due date advanced by one month.
        $this->assertEquals(
            $originalNextDue->copy()->addMonth()->format('Y-m-d'),
            $plan->fresh()->next_due_date->format('Y-m-d')
        );

        Carbon::setTestNow();
    }

    public function test_process_payment_plans_creates_installment_for_due_plans(): void
    {
        Carbon::setTestNow('2026-06-26');

        $invoice = Invoice::factory()->create([
            'total_amount' => 120.00,
            'is_installment' => false,
        ]);
        $plan = app(BillingService::class)->setupPaymentPlan($invoice, 3, 'monthly');

        // Force the plan to be due now.
        $plan->update(['next_due_date' => Carbon::now()->subDay()]);

        app(BillingService::class)->processPaymentPlans();

        $this->assertEquals(1, $invoice->fresh()->installments()->count());

        Carbon::setTestNow();
    }

    public function test_payment_plan_installments_relationship_does_not_match_child_invoices(): void
    {
        // Create a throwaway invoice first so the plan id and parent invoice id
        // diverge, exposing the relationship key mismatch.
        Invoice::factory()->create(['is_installment' => false]);

        $invoice = Invoice::factory()->create([
            'total_amount' => 120.00,
            'is_installment' => false,
        ]);
        $plan = app(BillingService::class)->setupPaymentPlan($invoice, 3, 'monthly');

        // Guard: ids actually differ, otherwise this test proves nothing.
        $this->assertNotEquals($plan->id, $invoice->id);

        app(PaymentPlanService::class)->createInstallmentInvoice($plan->fresh());

        // The child invoice exists and is linked to the parent invoice...
        $this->assertEquals(1, $invoice->fresh()->installments()->count());

        // ...but PaymentPlan::installments() keys on parent_invoice_id = plan.id,
        // so it finds nothing. Documents the bug in PaymentPlan.php:58-64.
        $this->assertEquals(0, $plan->fresh()->installments()->count());
    }
}
