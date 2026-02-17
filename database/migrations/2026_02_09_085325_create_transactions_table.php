<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->string('gateway');
            $table->decimal('amount', 10, 2);
            $table->string('currency');
            $table->string('status');
            $table->string('payment_method_id')->nullable();
            $table->text('error_message')->nullable();
            $table->json('raw_response')->nullable();
            $table->nullableMorphs('payable');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};