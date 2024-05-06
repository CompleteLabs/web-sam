<?php

namespace Database\Seeders;

use App\Models\PlanVisit;
use Illuminate\Database\Seeder;

class PlanVisitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PlanVisit::insert([
            [
                'user_id' => 1,
                'outlet_id' => 1,
                'tanggal_visit' => '2021-09-11',
            ],
            [
                'user_id' => 1,
                'outlet_id' => 2,
                'tanggal_visit' => '2021-09-11',
            ],
            [
                'user_id' => 1,
                'outlet_id' => 1,
                'tanggal_visit' => '2020-09-10',
            ],
        ]);
    }
}
