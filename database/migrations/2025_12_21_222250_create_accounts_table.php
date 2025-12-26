<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Self-referencing FK for Hierarchy (Composite Pattern)
            // If parent_id is null, it is a root account (e.g., Parent container)
            $table->foreignId('parent_id')->nullable()->constrained('accounts')->onDelete('restrict');

            $table->string('account_number')->unique();
            
            $table->enum('type', ['savings', 'checking', 'loan', 'investment']);
            
            // Stores the State Pattern status
            $table->string('status')->default('active')->index(); 
            // e.g., 'active', 'frozen', 'suspended', 'closed'
            
            $table->decimal('balance', 15, 2)->default(0.00);
            $table->string('currency', 3)->default('USD');
            
            // Stores the key for the Strategy Pattern (e.g., 'standard_savings', 'high_yield')
            $table->string('interest_strategy')->nullable(); 

            $table->timestamps();
            $table->softDeletes(); // Recommended for financial data safety
        });
    }

    public function down()
    {
        Schema::dropIfExists('accounts');
    }
};