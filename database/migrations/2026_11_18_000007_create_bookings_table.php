<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code', 30)->unique();
            $table->foreignId('customer_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $table->foreignId('costume_id')->constrained()->restrictOnDelete();
            $table->string('size', 5);
            $table->string('booker_name');
            $table->string('identity_number', 30);
            $table->string('phone', 30);
            $table->text('address');
            $table->date('rental_date')->index();
            $table->date('return_date')->index();
            $table->unsignedInteger('duration_days');
            $table->unsignedInteger('rental_price');
            $table->unsignedInteger('deposit');
            $table->unsignedInteger('shipping_fee')->default(0);
            $table->unsignedInteger('total');
            $table->string('status', 30)->default('waiting_confirmation')->index();
            $table->string('shipping_method', 20)->nullable();
            $table->string('ktp_path');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index(['costume_id', 'rental_date', 'return_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
