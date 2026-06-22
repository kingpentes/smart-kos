<?php

namespace Database\Seeders;

use App\Enums\BoardingHouseStatus;
use App\Enums\BoardingHouseType;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\BoardingHouse;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RailwaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buat Akun Admin
        User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
                'role' => UserRole::Admin->value,
                'status' => UserStatus::Active->value,
                'email_verified_at' => now(),
                'ai_trial_credits_remaining' => 5
            ]
        );

        // 2. Buat Akun Pemilik 1
        $pemilik = User::firstOrCreate(
            ['email' => 'pemilik1@pemilik.com'],
            [
                'name' => 'Pemilik 1',
                'password' => bcrypt('password'),
                'role' => UserRole::Owner->value,
                'status' => UserStatus::Active->value,
                'email_verified_at' => now(),
                'ai_trial_credits_remaining' => 5
            ]
        );

        // 3. Buat 3 Kos untuk Pemilik 1
        // Kos 1 (Dengan koordinat khusus)
        BoardingHouse::firstOrCreate(
            ['slug' => Str::slug('Kos Harapan Bangsa')],
            [
                'owner_id' => $pemilik->id,
                'name' => 'Kos Harapan Bangsa',
                'description' => 'Kos nyaman dan aman dengan fasilitas lengkap.',
                'address' => 'Jl. Harapan No. 1',
                'city' => 'Balikpapan',
                'district' => 'Balikpapan Selatan',
                'type' => BoardingHouseType::Mixed->value,
                'latitude' => -1.173656,
                'longitude' => 116.853037,
                'price_monthly' => 1500000,
                'deposit_amount' => 500000,
                'status' => BoardingHouseStatus::Published->value,
                'verified_at' => now(),
            ]
        );

        // Kos 2
        BoardingHouse::firstOrCreate(
            ['slug' => Str::slug('Kos Melati Indah')],
            [
                'owner_id' => $pemilik->id,
                'name' => 'Kos Melati Indah',
                'description' => 'Kos murah untuk mahasiswa dan pekerja.',
                'address' => 'Jl. Melati No. 2',
                'city' => 'Balikpapan',
                'district' => 'Balikpapan Tengah',
                'type' => BoardingHouseType::Male->value,
                'latitude' => -1.200000,
                'longitude' => 116.800000,
                'price_monthly' => 1200000,
                'deposit_amount' => 400000,
                'status' => BoardingHouseStatus::Published->value,
                'verified_at' => now(),
            ]
        );

        // Kos 3
        BoardingHouse::firstOrCreate(
            ['slug' => Str::slug('Kos Mawar Biru')],
            [
                'owner_id' => $pemilik->id,
                'name' => 'Kos Mawar Biru',
                'description' => 'Kos eksklusif fasilitas bintang lima.',
                'address' => 'Jl. Mawar No. 3',
                'city' => 'Balikpapan',
                'district' => 'Balikpapan Kota',
                'type' => BoardingHouseType::Female->value,
                'latitude' => -1.250000,
                'longitude' => 116.820000,
                'price_monthly' => 2000000,
                'deposit_amount' => 1000000,
                'status' => BoardingHouseStatus::PUBLISHED->value,
                'verified_at' => now(),
            ]
        );
    }
}
