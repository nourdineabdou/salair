<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*$user = User::create([
            'name' => 'Cheikh Ahmed Aloueimin',
            'email' => 'super-admin@gmail.com',
            'password' => bcrypt('password')
        ]);*/

        $user = new User();
        $user->name = 'Cheikh Ahmed Aloueimin';
        $user->email = 'super-admin@gmail.com';
        $user->password = bcrypt('password');
        $user->save();

        
    }

}
