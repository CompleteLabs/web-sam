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
        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('can_access_web')->default(true)->after('name');
            $table->string('filter_type')->nullable()->after('can_access_web');
            $table->json('filter_data')->nullable()->after('filter_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('can_access_web');
            $table->dropColumn('filter_type');
            $table->dropColumn('filter_data');
        });
    }
};
