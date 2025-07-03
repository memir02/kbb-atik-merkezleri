<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use League\Csv\Statement;

class AtikMerkeziSeeder extends Seeder
{
    public function run()
    {
        $csvPath = storage_path('app/atik_merkezleri.csv');

        if (!file_exists($csvPath)) {
            echo "CSV dosyası bulunamadı.\n";
            return;
        }

        $csv = Reader::createFromPath($csvPath, 'r');
        $csv->setHeaderOffset(0);

        $records = (new Statement())->process($csv);

        foreach ($records as $record) {
            // Anahtarları BOM karakterinden arındır
            $cleanRecord = [];
            foreach ($record as $key => $value) {
                $cleanKey = trim(str_replace("\xEF\xBB\xBF", '', $key));
                $cleanRecord[$cleanKey] = $value;
            }

            DB::table('atik_merkezleri')->insert([
                'title'      => $cleanRecord['Title'] ?? null,
                'content'    => $cleanRecord['Content'] ?? null,
                'lat'        => $cleanRecord['Lat'] ?? null,
                'lon'        => $cleanRecord['Lon'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        echo " Veri başarıyla yüklendi.\n";
    }
}
// Bu seeder, atık merkezleri verilerini CSV dosyasından okuyarak veritabanına ekler.
// CSV dosyasının başlık satırındaki anahtarlar BOM karakterinden arındırılır ve veriler veritabanına eklenir.
// Eğer CSV dosyası bulunamazsa, bir hata mesajı gösterilir.
// Seeder çalıştırıldığında, veritabanına atık merkezleri verileri eklenir ve işlem tamamlandığında bir başarı mesajı gösterilir.
// Seeder'ı çalıştırmak için terminalde şu komutu kullanabilirsiniz:
// php artisan db:seed --class=AtikMerkeziSeeder bu komut, AtikMerkeziSeeder sınıfını çalıştırarak veritabanına atık merkezleri verilerini ekler.
// Eğer veritabanını baştan oluşturmak isterseniz, önce migration'ları
// php artisan migrate:refresh komutunu kullanabilirsiniz. Bu komut, veritabanı tablolarını sıfırlar ve ardından seeder'ları çalıştırır.
// Ardından,php artisan db:seed komutunu kullanarak tüm seeder'ları çalıştırabilirsiniz
// php artisan serve hostu çalıştırmak için
