<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Credit packages — defined by owner, purchased by schools
        Schema::create('messaging_credit_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // e.g. "Starter Pack"
            $table->integer('sms_credits')->default(0);     // SMS messages included
            $table->integer('whatsapp_credits')->default(0);// WhatsApp messages included
            $table->decimal('price', 10, 2);                // Price in GHS
            $table->string('currency', 10)->default('GHS');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Credits balance per tenant
        Schema::create('messaging_credits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->integer('sms_credits')->default(0);
            $table->integer('whatsapp_credits')->default(0);
            $table->integer('sms_used')->default(0);        // lifetime total sent
            $table->integer('whatsapp_used')->default(0);   // lifetime total sent
            $table->timestamp('last_topped_up_at')->nullable();
            $table->timestamps();
            $table->unique('tenant_id');
        });

        // Payment records for credit purchases
        Schema::create('messaging_credit_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('package_id')->nullable()->constrained('messaging_credit_packages')->nullOnDelete();
            $table->string('receipt_number')->unique();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('GHS');
            $table->integer('sms_credits_added')->default(0);
            $table->integer('whatsapp_credits_added')->default(0);
            $table->enum('payment_method', ['cash', 'mobile_money', 'bank_transfer', 'card', 'paystack', 'other'])->default('paystack');
            $table->string('paystack_reference')->nullable();
            $table->string('paystack_status')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->text('notes')->nullable();
            $table->boolean('receipt_emailed')->default(false);
            $table->timestamps();
        });

        // Seed default packages
        $now = now();
        \DB::table('messaging_credit_packages')->insert([
            ['name' => 'Starter Pack',   'sms_credits' => 100,  'whatsapp_credits' => 50,   'price' => 15.00,  'description' => '100 SMS + 50 WhatsApp messages', 'sort_order' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Standard Pack',  'sms_credits' => 500,  'whatsapp_credits' => 250,  'price' => 60.00,  'description' => '500 SMS + 250 WhatsApp messages', 'sort_order' => 2, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Premium Pack',   'sms_credits' => 2000, 'whatsapp_credits' => 1000, 'price' => 200.00, 'description' => '2000 SMS + 1000 WhatsApp messages', 'sort_order' => 3, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Enterprise Pack','sms_credits' => 5000, 'whatsapp_credits' => 2500, 'price' => 450.00, 'description' => '5000 SMS + 2500 WhatsApp messages', 'sort_order' => 4, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('messaging_credit_payments');
        Schema::dropIfExists('messaging_credits');
        Schema::dropIfExists('messaging_credit_packages');
    }
};
