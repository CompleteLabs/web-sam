<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Division::insert([
            [
                'badanusaha_id' => 1,
                'name' => 'MSIS',
            ],
            [
                'badanusaha_id' => 2,
                'name' => 'ORAIMO',
            ],
            [
                'badanusaha_id' => 2,
                'name' => 'TECNO',
            ],
            [
                'badanusaha_id' => 2,
                'name' => 'REALME',
            ],
            [
                'badanusaha_id' => 3,
                'name' => '-',
            ],
        ]);
    }
}
