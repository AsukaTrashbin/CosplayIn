<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained()->restrictOnDelete();
            $table->date('actual_return_date')->nullable();
            $table->string('condition', 20)->nullable();
            $table->string('method', 20);
            $table->string('tracking_number')->nullable();
            $table->string('cod_location')->nullable();
            $table->string('cod_time', 30)->nullable();
            $table->unsignedInteger('days_late')->default(0);
            $table->unsignedInteger('late_fee')->default(0);
            $table->unsignedInteger('damage_claim')->default(0);
            $table->unsignedInteger('deposit_deduction')->default(0);
            $table->unsignedInteger('deposit_refunded')->default(0);
            $table->text('tenant_note')->nullable();
            $table->timestamps();
            $table->index('actual_return_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('returns');
    }
};
