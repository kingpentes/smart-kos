<?php

namespace Database\Seeders;

use App\Enums\BoardingHouseStatus;
use App\Models\BoardingHouse;
use App\Models\Facility;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'Admin SMART KOST',
            'email' => 'admin@smartkost.test',
        ]);

        $owner = User::factory()->owner()->create([
            'name' => 'Pemilik Kos',
            'email' => 'pemilik@smartkost.test',
        ]);

        User::factory()->tenant()->create([
            'name' => 'Penyewa Kos (Gratis)',
            'email' => 'penyewa@smartkost.test',
        ]);

        $premiumTenant = User::factory()->tenant()->create([
            'name' => 'Penyewa Kos (Premium)',
            'email' => 'premium@smartkost.test',
        ]);

        \App\Models\Subscription::factory()->create([
            'user_id' => $premiumTenant->id,
            'plan_code' => 'ai_premium',
            'ai_request_limit' => -1,
        ]);

        $facilities = collect(['WiFi', 'AC', 'Kamar Mandi Dalam', 'Parkir Motor', 'Dapur Bersama', 'Laundry'])
            ->map(fn (string $name): Facility => Facility::query()->firstOrCreate([
                'slug' => Str::slug($name),
            ], [
                'name' => $name,
            ]));

        BoardingHouse::factory()
            ->count(3)
            ->published($admin)
            ->for($owner, 'owner')
            ->create()
            ->each(function (BoardingHouse $boardingHouse) use ($facilities): void {
                $boardingHouse->facilities()->sync($facilities->random(3)->pluck('id'));

                Room::factory()
                    ->count(5)
                    ->for($boardingHouse)
                    ->sequence(fn ($sequence) => [
                        'room_number' => (string) ($sequence->index + 1),
                        'price_monthly' => $boardingHouse->price_monthly,
                    ])
                    ->create();

                $boardingHouse->photos()->create([
                    'path' => 'boarding-houses/sample.jpg',
                    'caption' => 'Foto utama kos',
                    'is_primary' => true,
                    'sort_order' => 0,
                ]);

                $boardingHouse->rules()->createMany([
                    ['key' => 'Jam malam', 'value' => 'Tamu maksimal sampai pukul 22.00.'],
                    ['key' => 'Kebersihan', 'value' => 'Penghuni wajib menjaga kebersihan area bersama.'],
                ]);
            });

        BoardingHouse::factory()
            ->pending()
            ->for($owner, 'owner')
            ->create([
                'name' => 'Kos Menunggu Verifikasi',
                'slug' => 'kos-menunggu-verifikasi',
                'status' => BoardingHouseStatus::Pending,
            ]);
    }
}
