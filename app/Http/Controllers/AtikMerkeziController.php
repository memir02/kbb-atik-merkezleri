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
        $tumMerkezler = null;

        // Sadece filtre seçilmişse filtrelenmiş veri getir
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
        } else {
            // Ana sayfa için ilk 20 merkezi getir
            $tumMerkezler = AtikMerkezi::take(20)->get();
        }

        // index.blade.php sayfasına verileri gönder
        return view('index', compact('merkezler', 'tumMerkezler'));
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

    /**
     * API: Tek merkez bilgisi getir
     */
    public function getMerkez($id)
    {
        $merkez = AtikMerkezi::find($id);
        
        if (!$merkez) {
            return response()->json(['error' => 'Merkez bulunamadı'], 404);
        }
        
        return response()->json($merkez);
    }

    /**
     * API: Birden fazla merkez bilgisi getir
     */
    public function getMerkezler(Request $request)
    {
        $ids = $request->input('ids', []);
        
        if (empty($ids)) {
            return response()->json(['error' => 'Merkez ID\'leri belirtilmedi'], 400);
        }
        
        $merkezler = AtikMerkezi::whereIn('id', $ids)->get();
        
        return response()->json($merkezler);
    }

    /**
     * API: Infinite scroll için daha fazla merkez getir
     */
    public function loadMore(Request $request)
    {
        $offset = $request->input('offset', 0);
        $limit = 20;

        $merkezler = AtikMerkezi::skip($offset)->take($limit)->get();

        return response()->json([
            'merkezler' => $merkezler,
            'hasMore' => AtikMerkezi::count() > ($offset + $limit)
        ]);
    }
}
