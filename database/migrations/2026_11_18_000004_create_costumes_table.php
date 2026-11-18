<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('costumes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('character_name');
            $table->text('description');
            $table->text('accessories')->nullable();
            $table->string('gender', 20)->default('unisex')->index();
            $table->string('city')->index();
            $table->unsignedInteger('price_per_day');
            $table->unsignedInteger('deposit')->default(0);
            $table->boolean('is_popular')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('costumes');
    }
};
