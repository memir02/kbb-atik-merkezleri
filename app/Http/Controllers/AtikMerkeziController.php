<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AtikMerkezi;
use App\Http\Controllers\Controller;

class AtikMerkeziController extends Controller
{
    public function index(Request $request)
    {
        $merkezler = null;

        // Sadece filtre seçilmişse veri getir
        if ($request->has('filter') && is_array($request->filter)) {
            $query = AtikMerkezi::query();
            $query->where(function ($q) use ($request) {
                foreach ($request->filter as $type) {
                    // Filtre değerlerini gerçek content değerleriyle eşle
                    $searchTerm = $this->getSearchTerm($type);
                    $q->orWhere('content', 'like', '%' . $searchTerm . '%');
                }
            });
            $merkezler = $query->get();
        }

        // index.blade.php sayfasına verileri gönder
        return view('index', compact('merkezler'));
    }

    /**
     * Filtre değerlerini veritabanındaki gerçek değerlerle eşleştir
     */
    private function getSearchTerm($filterValue)
    {
        $searchTerms = [
            'mobil' => 'MOBİL ATIK GETİRME MERKEZİ',
            'bitkisel' => 'BİTKİSEL ATIK YAĞ',
            'cam' => 'ATIK CAM',
            'tekstil' => 'TEKSTİL KUMBARASI',
            'gecici' => 'ATIK GEÇİCİ DEPOLAMA ÜNİTESİ',
            'ilac' => 'ATIK İLAÇ',
            'sinif1' => '1. SINIF ATIK GETİRME MERKEZİ',
            'inert' => 'İNERT ATIK',
            'hafriyat' => 'HAFRİYAT'
        ];

        return $searchTerms[$filterValue] ?? $filterValue;
    }
}
