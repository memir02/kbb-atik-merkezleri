<?php

namespace App\Services;

use App\Models\AtikMerkezi;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Search Service
 * Arama işlemlerini yönetir - FULLTEXT ile geliştirilmiş
 */
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
        've', 'ile', 'da', 'de', 'ta', 'te',
        'bir', 'bu', 'şu', 'o', 'ki', 'mi', 'mu', 'mı', 'mü'
    ];

    /**
     * Türkçe karakter normalizasyon mapping
     */
    private const TURKISH_CHAR_MAP = [
        'ç' => 'c', 'Ç' => 'C',
        'ğ' => 'g', 'Ğ' => 'G',
        'ı' => 'i', 'I' => 'I',
        'ö' => 'o', 'Ö' => 'O',
        'ş' => 's', 'Ş' => 'S',
        'ü' => 'u', 'Ü' => 'U'
    ];

    /**
     * Filtre değerlerini gerçek content değerleriyle eşleştir
     */
    private const FILTER_MAPPINGS = [
        'mobil' => ['MOBİL ATIK GETİRME MERKEZİ', 'MOBIL ATIK GETIRME MERKEZI'],
        'plastik' => ['MOBİL ATIK GETİRME MERKEZİ', 'ATIK GEÇİCİ DEPOLAMA ÜNİTESİ', 'PLASTIK'],
        'metal' => ['MOBİL ATIK GETİRME MERKEZİ', 'ATIK GEÇİCİ DEPOLAMA ÜNİTESİ', 'METAL'],
        'cam' => ['MOBİL ATIK GETİRME MERKEZİ', 'ATIK GEÇİCİ DEPOLAMA ÜNİTESİ', 'ATIK CAM', 'CAM'],
        'kağıt' => ['MOBİL ATIK GETİRME MERKEZİ', 'ATIK GEÇİCİ DEPOLAMA ÜNİTESİ', 'KAĞIT', 'KAGIT'],
        'kagit' => ['MOBİL ATIK GETİRME MERKEZİ', 'ATIK GEÇİCİ DEPOLAMA ÜNİTESİ', 'KAĞIT', 'KAGIT'],
        'pil' => ['MOBİL ATIK GETİRME MERKEZİ', 'PİL', 'PIL'],
        'bitkisel' => ['BİTKİSEL ATIK YAĞ', 'BITKISEL ATIK YAG'],
        'atıkcam' => ['ATIK CAM'],
        'tekstil' => ['TEKSTİL KUMBARASI', 'TEKSTIL KUMBARASI', 'TEKSTİL', 'TEKSTIL'],
        'gecici' => ['ATIK GEÇİCİ DEPOLAMA ÜNİTESİ', 'GECICI DEPOLAMA'],
        'ilac' => ['ATIK İLAÇ', 'ATIK ILAC', 'İLAÇ', 'ILAC'],
        'ilaç' => ['ATIK İLAÇ', 'ATIK ILAC', 'İLAÇ', 'ILAC'],
        'sinif1' => ['1. SINIF ATIK GETİRME MERKEZİ', '1. SINIF', 'SINIF'],
        'inert' => ['İNERT ATIK', 'INERT ATIK'],
        'hafriyat' => ['HAFRİYAT', 'HAFRIYAT']
    ];

    /**
     * Gelişmiş adres bazlı akıllı arama yapar
     */
    public function searchByAddress(string $searchTerm): Collection
    {
        // Cache anahtarı oluştur
        $cacheKey = 'search_' . md5($searchTerm);
        
        return Cache::remember($cacheKey, 300, function() use ($searchTerm) {
            // Önce normalize edilmiş arama terimi ile FULLTEXT arama yap
            $normalizedTerm = $this->normalizeSearchTerm($searchTerm);
            $results = $this->performFulltextSearch($normalizedTerm);
            
            // Eğer FULLTEXT'ten yeterli sonuç yoksa, fuzzy arama yap
            if ($results->count() < 5) {
                $fuzzyResults = $this->performFuzzySearch($normalizedTerm);
                $results = $results->merge($fuzzyResults)->unique('id');
            }
            
            // Sonuçları skorla ve sırala
            return $this->scoreAndSortResults($results, $searchTerm);
        });
    }

    /**
     * FULLTEXT search yapar
     */
    private function performFulltextSearch(string $term): Collection
    {
        if (empty(trim($term))) {
            return collect();
        }

        // Anlamlı kelimeler çıkar
        $meaningfulWords = $this->extractMeaningfulWords($term);
        
        if (empty($meaningfulWords)) {
            return collect();
        }

        // FULLTEXT arama sorgusu
        $searchQuery = implode(' ', $meaningfulWords);
        
        return AtikMerkezi::select('*')
            ->selectRaw('MATCH(title, content, adres) AGAINST(? IN NATURAL LANGUAGE MODE) as relevance_score', [$searchQuery])
            ->whereRaw('MATCH(title, content, adres) AGAINST(? IN NATURAL LANGUAGE MODE)', [$searchQuery])
            ->orderBy('relevance_score', 'desc')
            ->limit(20)
            ->get();
    }

    /**
     * Fuzzy search - FULLTEXT yetersizse
     */
    private function performFuzzySearch(string $term): Collection
    {
        $meaningfulWords = $this->extractMeaningfulWords($term);
        
        if (empty($meaningfulWords)) {
            return collect();
        }

        $query = AtikMerkezi::query();
        
        // Her anlamlı kelime için LIKE sorgusu
        $query->where(function ($q) use ($meaningfulWords) {
            foreach ($meaningfulWords as $word) {
                $q->where(function ($subQ) use ($word) {
                    $subQ->where('title', 'like', '%' . $word . '%')
                         ->orWhere('content', 'like', '%' . $word . '%')
                         ->orWhere('adres', 'like', '%' . $word . '%');
                });
            }
        });

        // Alternatif: OR mantığı ile daha geniş arama
        $query->orWhere(function ($q) use ($meaningfulWords) {
            foreach ($meaningfulWords as $word) {
                $q->orWhere('title', 'like', '%' . $word . '%')
                  ->orWhere('content', 'like', '%' . $word . '%')
                  ->orWhere('adres', 'like', '%' . $word . '%');
            }
        });

        return $query->limit(15)->get();
    }

    /**
     * Arama terimini normalize et
     */
    private function normalizeSearchTerm(string $term): string
    {
        // Küçük harfe çevir
        $normalized = mb_strtolower(trim($term), 'UTF-8');
        
        // Türkçe karakterleri normalize et (opsiyonel - hem normal hem normalize arama yapacağız)
        $normalizedAscii = strtr($normalized, self::TURKISH_CHAR_MAP);
        
        // Fazla boşlukları temizle
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        
        return $normalized;
    }

    /**
     * Anlamlı kelimeleri çıkar
     */
    private function extractMeaningfulWords(string $term): array
    {
        $words = explode(' ', $term);
        
        return array_filter($words, function($word) {
            $word = trim($word);
            return !empty($word) && 
                   mb_strlen($word, 'UTF-8') > 2 && 
                   !in_array($word, self::STOP_WORDS);
        });
    }

    /**
     * Sonuçları skorla ve sırala
     */
    private function scoreAndSortResults(Collection $results, string $originalTerm): Collection
    {
        $originalTerm = mb_strtolower($originalTerm, 'UTF-8');
        
        return $results->map(function ($merkez) use ($originalTerm) {
            $score = 0;
            $title = mb_strtolower($merkez->title, 'UTF-8');
            $content = mb_strtolower($merkez->content, 'UTF-8');
            $adres = mb_strtolower($merkez->adres, 'UTF-8');
            
            // Tam eşleşme bonusu
            if (str_contains($title, $originalTerm)) $score += 100;
            if (str_contains($adres, $originalTerm)) $score += 80;
            if (str_contains($content, $originalTerm)) $score += 60;
            
            // Kelime bazlı skorlama
            $words = explode(' ', $originalTerm);
            foreach ($words as $word) {
                if (mb_strlen($word, 'UTF-8') > 2) {
                    if (str_contains($title, $word)) $score += 20;
                    if (str_contains($adres, $word)) $score += 15;
                    if (str_contains($content, $word)) $score += 10;
                }
            }
            
            // FULLTEXT relevance score varsa ekle
            if (isset($merkez->relevance_score)) {
                $score += $merkez->relevance_score * 50;
            }
            
            $merkez->search_score = $score;
            return $merkez;
        })
        ->sortByDesc('search_score')
        ->values();
    }

    /**
     * Filtre bazlı arama yapar
     */
    public function searchByFilters(array $filters): Collection
    {
        if (empty($filters)) {
            return collect();
        }

        $cacheKey = 'filters_' . implode('_', $filters);
        
        // AKILLI CACHE SİSTEMİ - Sadece ID'leri cache'le, packet limiti güvenli
        $cachedIds = Cache::remember($cacheKey, 600, function() use ($filters) {
            $query = AtikMerkezi::query();
            
            $query->where(function ($q) use ($filters) {
                foreach ($filters as $filter) {
                    $searchTerms = $this->getSearchTermsForFilter($filter);
                    
                    if (!empty($searchTerms)) {
                        $q->orWhere(function ($subQ) use ($searchTerms) {
                            foreach ($searchTerms as $term) {
                                $subQ->orWhere('content', 'like', '%' . $term . '%');
                            }
                        });
                    }
                }
            });
            
            // Sadece ID'leri al ve cache'le (küçük boyut, packet safe)
            return $query->pluck('id')->toArray();
        });
        
        // Cache'ten gelen ID'lerle asıl verileri fetch et
        if (empty($cachedIds)) {
            return collect();
        }
        
        $results = AtikMerkezi::whereIn('id', $cachedIds)->get();
        
        return $results;
    }

    /**
     * Filtre değerini gerçek arama terimlerine çevir
     */
    private function getSearchTermsForFilter(string $filterValue): array
    {
        return self::FILTER_MAPPINGS[$filterValue] ?? [$filterValue];
    }

    /**
     * Arama önerileri getir
     */
    public function getSearchSuggestions(string $query, int $limit = 5): array
    {
        if (mb_strlen($query, 'UTF-8') < 2) {
            return [];
        }

        $cacheKey = 'suggestions_' . md5($query) . '_' . $limit;
        
        return Cache::remember($cacheKey, 1800, function() use ($query, $limit) {
            // Popüler adres parçalarını getir
            $suggestions = AtikMerkezi::select('adres')
                ->where('adres', 'like', '%' . $query . '%')
                ->groupBy('adres')
                ->limit($limit * 2)
                ->get()
                ->pluck('adres')
                ->map(function ($adres) use ($query) {
                    // Adres'ten ana bölüm çıkar
                    $parts = explode(',', $adres);
                    return trim($parts[0]);
                })
                ->unique()
                ->take($limit)
                ->values()
                ->toArray();

            return $suggestions;
        });
    }

    /**
     * Popüler aramaları getir
     */
    public function getPopularSearches(): array
    {
        return [
            'yazır', 'selçuklu', 'meram', 'karatay',
            'bosna hersek', 'cumhuriyet', 'horozluhan',
            'plastik', 'cam', 'pil', 'bitkisel yağ'
        ];
    }

    /**
     * Veritabanı performansını optimize et
     */
    public function optimizeDatabase(): bool
    {
        try {
            // Sık kullanılan alanlar için ekstra index'ler
            DB::statement('CREATE INDEX IF NOT EXISTS idx_atik_merkezleri_title ON atik_merkezleri (title)');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_atik_merkezleri_adres ON atik_merkezleri (adres)');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_atik_merkezleri_content ON atik_merkezleri (content)');
            
            // Composite index - birden fazla alan için
            DB::statement('CREATE INDEX IF NOT EXISTS idx_atik_merkezleri_search ON atik_merkezleri (title, adres)');
            
            return true;
        } catch (\Exception $e) {
            Log::error('Database optimization failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Arama istatistiklerini logla (gelecekte analytics için)
     */
    public function logSearchStats(string $query, int $resultCount, float $executionTime): void
    {
        // Production'da log devre dışı - gerekirse analytics sistemi eklenebilir
    }
} 