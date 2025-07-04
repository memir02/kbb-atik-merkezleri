<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AtikMerkezi;
use Illuminate\Support\Facades\Http;

class ReverseGeocodeAtikMerkezleri extends Command
{
    protected $signature = 'atikmerkezi:adresleri-guncelle';
    protected $description = 'Tüm atık merkezleri için enlem ve boylamdan adres bilgisi çek ve veritabanına kaydet';

    public function handle()
    {
        $this->info('Adres güncelleme işlemi başladı...');

        $merkezler = AtikMerkezi::whereNull('adres')->get();

        foreach ($merkezler as $merkez) {
            $this->info("→ {$merkez->title} adres alınıyor...");

            $response = Http::withHeaders([
                'User-Agent' => 'LaravelApp/1.0 (ceran.emir1905@gmail.com)'
            ])->get('https://nominatim.openstreetmap.org/reverse', [
                'format' => 'json',
                'lat' => $merkez->lat,
                'lon' => $merkez->lon,
                'addressdetails' => 1,
            ]);

            if ($response->successful() && isset($response['display_name'])) {
                $merkez->adres = $response['display_name'];
                $merkez->save();
                $this->info("✅ Adres kaydedildi: {$merkez->adres}");
                sleep(1); // Nominatim rate limit koruması
            } else {
                $this->error("❌ Adres alınamadı.");
                $this->error('HTTP durumu: ' . $response->status());
                $this->warn($response->body());
            }
            
            
        }

        $this->info('Tüm kayıtlar işlendi.');
        return 0;
    }
}
