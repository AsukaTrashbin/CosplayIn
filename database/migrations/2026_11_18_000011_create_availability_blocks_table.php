<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('availability_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('costume_id')->constrained()->cascadeOnDelete();
            $table->string('size', 5);
            $table->date('date')->index();
            $table->string('type', 20)->default('booked');
            $table->foreignId('booking_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->index(['costume_id', 'size', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('availability_blocks');
    }
};
