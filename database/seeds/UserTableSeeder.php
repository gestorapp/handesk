<?php

use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(User::class)->create([
            'email'     => 'admin@handesk.com',
            'password'  => bcrypt('admin'),
            'admin'     => true,
        ]);
    }
}