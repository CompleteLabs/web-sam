<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('custom_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->string('type');
            $table->text('options')->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('system_defined')->default(false);
            $table->string('entity_type')->nullable();
            $table->string('apply_entity_type')->nullable();
            $table->unsignedBigInteger('apply_entity_id')->nullable();

            $table->json('validation_rules')->nullable();

            $table->timestamps();

            $table->index(['apply_entity_type', 'apply_entity_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_attributes');
    }
};
