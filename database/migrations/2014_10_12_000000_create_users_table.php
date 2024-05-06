<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('nama_lengkap');
            $table->foreignId('badanusaha_id');
            $table->foreignId('divisi_id');
            $table->foreignId('region_id');
            $table->foreignId('cluster_id');
            $table->foreignId('role_id');
            $table->foreignId('tm_id');
            $table->string('password');
            $table->string('id_notif')->nullable();
            $table->rememberToken();
            $table->string('profile_photo_path')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
