<?php

use App\Settings;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Running DatabaseSeeder');

        $this->call('UserTableSeeder');

        Settings::create();

        /*$teams = factory(Team::class,4)->create();
    $teams->each(function($team){
    $team->memberships()->create([
    "user_id" => factory(User::class)->create()->id
    ]);
    $team->tickets()->createMany( factory(Ticket::class,4)->make()->toArray() );
    });

    factory(Ticket::class)->create();
     */
    }
}
