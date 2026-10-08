<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('owner_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('receipt_number')->unique();     // Auto-generated e.g. RCP-2024-00001
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('GHS');
            $table->date('payment_date');
            $table->enum('payment_method', ['cash', 'mobile_money', 'bank_transfer', 'card', 'other']);
            $table->string('reference')->nullable();        // Bank ref, MoMo ref, etc.
            $table->string('description')->nullable();      // What the payment is for
            $table->integer('months_paid')->default(1);     // How many months this covers
            $table->text('notes')->nullable();
            $table->string('receipt_path')->nullable();     // Path to generated PDF receipt
            $table->boolean('receipt_emailed')->default(false);
            $table->timestamp('receipt_emailed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('owner_payments');
    }
};
