<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;

class PosAccountSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            ['full_name' => 'Ana Reyes', 'email' => 'ana.reyes@example.com', 'phone' => '0917 123 4567'],
            ['full_name' => 'Ben Santos', 'email' => 'ben.santos@example.com', 'phone' => '0918 234 5678'],
            ['full_name' => 'Carla Cruz', 'email' => 'carla.cruz@example.com', 'phone' => '0919 345 6789'],
            ['full_name' => 'Daniel Lim', 'email' => 'daniel.lim@example.com', 'phone' => '0920 456 7890'],
            ['full_name' => 'Ella Garcia', 'email' => 'ella.garcia@example.com', 'phone' => '0921 567 8901'],
        ];
        $users = [
            ['username' => 'admin', 'full_name' => 'Alex Mendoza', 'role' => 'Administrator'],
            ['username' => 'manager1', 'full_name' => 'Bianca Flores', 'role' => 'Manager'],
            ['username' => 'cashier1', 'full_name' => 'Carlos Ramos', 'role' => 'Cashier'],
            ['username' => 'cashier2', 'full_name' => 'Diane Torres', 'role' => 'Cashier'],
            ['username' => 'stock1', 'full_name' => 'Ethan Bautista', 'role' => 'Inventory Staff'],
        ];

        foreach ($customers as $customer) {
            Customer::updateOrCreate(['email' => $customer['email']], $customer);
        }
        foreach ($users as $user) {
            User::updateOrCreate(['username' => $user['username']], $user);
        }
    }
}
