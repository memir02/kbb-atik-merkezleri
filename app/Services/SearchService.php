<?php

namespace App\Services;

use App\Models\AtikMerkezi;
use Illuminate\Support\Collection;

class SearchService
{
    /**
     * Anlamsız kelimeler (stop words)
     */
    private const STOP_WORDS = [
        'mahallesi', 'mahalle', 'mah', 'mh',
        'caddesi', 'cadde', 'cad', 'cd', 
        'sokağı', 'sokak', 'sok', 'sk',
        'bulvarı', 'bulvar', 'blv',
        'yolu', 'yol',
        'meydanı', 'meydan',
        've', 'ile', 'da', 'de', 'ta', 'te'
    ];

    /**
     * Filtre değerlerini gerçek content değerleriyle eşleştir
     */
    private const FILTER_MAPPINGS = [
        'plastik' => 'MOBİL ATIK GETİRME MERKEZİ',
        'metal' => 'MOBİL ATIK GETİRME MERKEZİ',
        'cam' => 'MOBİL ATIK GETİRME MERKEZİ',
        'karton' => 'MOBİL ATIK GETİRME MERKEZİ',
        'kağıt' => 'MOBİL ATIK GETİRME MERKEZİ',
        'pil' => 'MOBİL ATIK GETİRME MERKEZİ',
        'mobil' => 'MOBİL ATIK GETİRME MERKEZİ',
        'bitkisel' => 'BİTKİSEL ATIK YAĞ',
        'atıkcam' => 'ATIK CAM',
        'tekstil' => 'TEKSTİL KUMBARASI',
        'gecici' => 'ATIK GEÇİCİ DEPOLAMA ÜNİTESİ',
        'ilac' => 'ATIK İLAÇ',
        'sinif1' => '1. SINIF ATIK GETİRME MERKEZİ',
        'inert' => 'İNERT ATIK',
        'hafriyat' => 'HAFRİYAT'
    ];

    /**
     * Adres bazlı akıllı arama yapar
     */
    public function searchByAddress(string $searchTerm): Collection
    {
        // Kelimeyi parçala ve temizle
        $words = explode(' ', strtolower(trim($searchTerm)));
        $meaningfulWords = array_filter($words, function($word) {
            $word = trim($word);
            return !empty($word) && 
                   strlen($word) > 2 && 
                   !in_array($word, self::STOP_WORDS);
        });
        
        if (empty($meaningfulWords)) {
            return collect(); // Boş sonuç döndür
        }
        
        // Anlamlı kelimelerle arama yap
        $query = AtikMerkezi::query();
        $query->where(function ($q) use ($meaningfulWords) {
            foreach ($meaningfulWords as $word) {
                $q->where('adres', 'like', '%' . $word . '%');
            }
        });
        
        // Title'da da ara (opsiyonel olarak)
        $query->orWhere(function ($q) use ($meaningfulWords) {
            foreach ($meaningfulWords as $word) {
                $q->where('title', 'like', '%' . $word . '%');
            }
        });
        
        return $query->get();
    }

    /**
     * Filtre bazlı arama yapar
     */
    public function searchByFilters(array $filters): Collection
    {
        if (empty($filters)) {
            return collect();
        }

        $query = AtikMerkezi::query();
        $query->where(function ($q) use ($filters) {
            foreach ($filters as $type) {
                $searchTerm = $this->getSearchTermForFilter($type);
                $q->orWhere('content', 'like', '%' . $searchTerm . '%');
            }
        });
        
        return $query->get();
    }

    /**
     * Filtre değerini gerçek arama terimine çevir
     */
    private function getSearchTermForFilter(string $filterValue): string
    {
        return self::FILTER_MAPPINGS[$filterValue] ?? $filterValue;
    }
} 