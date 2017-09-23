<?php

use Illuminate\Database\Seeder;
use App\User;

class UpdateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Running UpdateSeeder...');

        $this->call('DateFormatsSeeder');

        if (!$admin = User::where('admin',1)->first()) {
            $this->call('UserTableSeeder');
        }

        Cache::flush();
    }
}
