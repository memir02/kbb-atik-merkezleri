<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Örnek kullanıcı (isteğe bağlı)
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // AtikMerkeziSeeder çağrılıyor
        $this->call([
            AtikMerkeziSeeder::class,
        ]);
    }
}
// Bu seeder, veritabanını başlatmak için kullanılır ve AtikMerkeziSeeder'ı çağırır.