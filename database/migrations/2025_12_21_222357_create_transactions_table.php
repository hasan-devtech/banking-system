<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique(); // Safe exposure for API consumers
            
            // Nullable because a Deposit has no 'from', Withdrawal has no 'to'
            $table->foreignId('from_account_id')->nullable()->constrained('accounts');
            $table->foreignId('to_account_id')->nullable()->constrained('accounts');
            
            $table->decimal('amount', 15, 2);
            $table->enum('type', ['deposit', 'withdrawal', 'transfer', 'fee', 'interest']);
            
            // Critical for Chain of Responsibility & Retry Logic
            $table->string('status')->default('pending_approval')->index();
            // Expected values: 'pending_approval', 'processing', 'completed', 'failed', 'rejected', 'failed_waiting_for_funds'
            
            // Adapter Pattern Support
            $table->string('payment_provider')->nullable(); // e.g., 'stripe', 'paypal'
            $table->string('provider_transaction_id')->nullable(); // e.g., 'ch_1Mn...'
            
            $table->json('metadata')->nullable(); // For extra context
            
            $table->timestamps();
            
            // Index for faster queries on large transaction logs
            $table->index(['from_account_id', 'created_at']);
            $table->index(['to_account_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};