<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->create([
            'name' => 'Admin BatikPOS',
            'email' => 'admin@batikpos.test',
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

        User::query()->create([
            'name' => 'Kasir BatikPOS',
            'email' => 'kasir@batikpos.test',
            'role' => User::ROLE_CASHIER,
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

        User::query()->create([
            'name' => 'Viewer BatikPOS',
            'email' => 'viewer@batikpos.test',
            'role' => User::ROLE_VIEWER,
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

        $this->call(DemoInventorySeeder::class);
    }
}
