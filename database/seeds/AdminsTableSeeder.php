<?php

use Illuminate\Database\Seeder;
use App\Models\Admin;

class AdminsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $data = [
            [
                'username' => 'defara',
                'email' => 'defara@qq.com',
                'first_name' => 'yi',
                'last_name' => 'sheldon',
                'password' => bcrypt('123456'),
            ],
        ];

        foreach ($data as $sd) {
            $user = new Admin();
            $user->username = $sd['username'];
            $user->email = $sd['email'];
            $user->first_name = $sd['first_name'];
            $user->last_name = $sd['last_name'];
            $user->password = $sd['password'];
            $user->save();
        }
    }
}
