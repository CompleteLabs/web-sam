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
        Schema::create('custom_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->morphs('entity');
            $table->unsignedBigInteger('custom_attribute_id');
            $table->text('value');
            $table->timestamps();

            $table->foreign('custom_attribute_id')->references('id')->on('custom_attributes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_attribute_values');
    }
};
