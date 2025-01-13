<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payment_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->integer('total_installments');
            $table->decimal('installment_amount', 10, 2);
            $table->string('frequency');
            $table->timestamp('start_date');
            $table->timestamp('next_due_date');
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('parent_invoice_id')->nullable()->constrained('invoices')->onDelete('cascade');
            $table->boolean('is_installment')->default(false);
        });
    }

    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['parent_invoice_id']);
            $table->dropColumn(['parent_invoice_id', 'is_installment']);
        });
        Schema::dropIfExists('payment_plans');
    }
};