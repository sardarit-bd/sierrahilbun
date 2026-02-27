<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_addresses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('label')->nullable();   

            // Recipient
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone', 20)->nullable();

            // Street
            $table->string('address_line1');
            $table->string('address_line2')->nullable(); 

            // USA specific
            $table->string('city');
            $table->char('state', 2);                   
            $table->string('zip_code', 10);             

            $table->boolean('is_default')->default(false);

            $table->timestamps();

            $table->index('user_id');
            $table->index(['user_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_addresses');
    }
};