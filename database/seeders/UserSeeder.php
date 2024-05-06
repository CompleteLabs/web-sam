<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::insert([
            [
                'username' => 'farid',
                'tm_id' => 1,
                'nama_lengkap' => 'RADEN FARID LESMANA',
                'region_id' => 17,
                'divisi_id' => 5,
                'badanusaha_id' => 3,
                'cluster_id' => 76,
                'role_id' => 1,
                'password' => bcrypt('complete123'),
            ],
            [
                'username' => 'robby',
                'tm_id' => 2,
                'nama_lengkap' => 'ROBBY AGUSTINA',
                'region_id' => 17,
                'divisi_id' => 5,
                'badanusaha_id' => 3,
                'cluster_id' => 76,
                'role_id' => 1,
                'password' => bcrypt('complete123'),
            ],
            [
                'username' => 'aritonang',
                'tm_id' => 3,
                'nama_lengkap' => 'RHAMA ARITONANG',
                'region_id' => 17,
                'divisi_id' => 5,
                'badanusaha_id' => 3,
                'cluster_id' => 76,
                'role_id' => 1,
                'password' => bcrypt('complete123'),
            ],
            [
                'username' => 'admin',
                'tm_id' => 4,
                'nama_lengkap' => 'NAMA ADMIN',
                'region_id' => 17,
                'divisi_id' => 5,
                'badanusaha_id' => 3,
                'cluster_id' => 76,
                'role_id' => 5,
                'password' => bcrypt('complete123'),
            ],
        ]);
    }
}
