<?php

use Illuminate\Database\Seeder;
use App\Models\ServiceUser;

class ServiceUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ServiceUser::truncate();
        factory(ServiceUser::class, 20)->create();
    }
}
