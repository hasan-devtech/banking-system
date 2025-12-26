<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('scheduled_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_account_id')->constrained('accounts');
            $table->foreignId('target_account_id')->nullable()->constrained('accounts');
            
            $table->decimal('amount', 15, 2);
            $table->string('description')->nullable();
            
            // Logic definition
            $table->string('cron_expression'); // e.g., "0 0 1 * *" (Monthly)
            
            $table->timestamp('next_run_at')->index();
            $table->timestamp('last_run_at')->nullable();
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('scheduled_transactions');
    }
};