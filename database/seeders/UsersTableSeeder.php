<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //$users = User::factory()->count(3)->make();

        /* factory(User::class, 3)->create()
        ->each(function($user) {
            $user->role()->save(factory(Role::class)->make());
        }); */

        $user = User::factory()->count(3)->create()
        ->each(function($user) {
            $user->role()->save(Role::factory()->make());
        });

        // Seed test users
        /* $user = User::factory()->count(3)->create();
        $users = User::All();
        foreach ($users as $user) {
            $user->hasAttached(
                Role::factory()->count(3),
            )
            ->create();
            
        } */
    }
}
