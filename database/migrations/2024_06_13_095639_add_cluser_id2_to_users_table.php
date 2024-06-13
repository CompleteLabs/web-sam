<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCluserId2ToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('cluster_id2')->nullable()->after('cluster_id'); // Menambah kolom cluster_id2 di bawah cluster_id
            $table->foreign('cluster_id2')->references('id')->on('clusters'); // Menambah constraint foreign key ke tabel clusters
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['cluster_id2']); // Menghapus constraint foreign key
            $table->dropColumn('cluster_id2'); // Menghapus kolom cluster_id2
        });
    }
}
