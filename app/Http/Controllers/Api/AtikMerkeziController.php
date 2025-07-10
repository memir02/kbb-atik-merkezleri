<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\AtikMerkeziService;
use App\Services\SearchService;
use App\Services\LocationService;

/**
 * AtikMerkeziController
 * Atık merkezi API kontrolcüsü
 */
class AtikMerkeziController extends Controller
{
    protected $atikMerkeziService;
    protected $searchService;
    protected $locationService;

    public function __construct(
        AtikMerkeziService $atikMerkeziService,
        SearchService $searchService,
        LocationService $locationService
    ) {
        $this->atikMerkeziService = $atikMerkeziService;
        $this->searchService = $searchService;
        $this->locationService = $locationService;
    }

    /**
     * Tek merkez bilgisi getir
     */
    public function show($id)
    {
        $merkez = $this->atikMerkeziService->getMerkezById($id);
        
        if (!$merkez) {
            return response()->json([
                'error' => 'Merkez bulunamadı',
                'message' => 'Belirtilen ID ile eşleşen atık merkezi bulunamadı.'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $merkez
        ]);
    }

    /**
     * Birden fazla merkez bilgisi getir
     */
    public function getMultiple(Request $request)
    {
        $ids = $request->input('ids', []);
        
        if (empty($ids)) {
            return response()->json([
                'error' => 'Geçersiz parametre',
                'message' => 'Merkez ID\'leri belirtilmedi'
            ], 400);
        }
        
        $merkezler = $this->atikMerkeziService->getMerkezlerByIds($ids);
        
        return response()->json([
            'success' => true,
            'data' => $merkezler,
            'count' => $merkezler->count()
        ]);
    }

    /**
     * Infinite scroll için daha fazla merkez getir
     */
    public function loadMore(Request $request)
    {
        $offset = $request->input('offset', 0);
        $limit = min($request->input('limit', 20), 50); // Maksimum 50

        $result = $this->atikMerkeziService->loadMoreMerkezler($offset, $limit);

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }

    /**
     * Konuma göre en yakın merkezleri getir
     */
    public function nearest(Request $request)
    {
        $lat = $request->input('lat');
        $lon = $request->input('lon');
        $limit = min($request->input('limit', 10), 20); // Maksimum 20

        if (!$this->locationService->validateCoordinates($lat, $lon)) {
            return response()->json([
                'error' => 'Geçersiz koordinat',
                'message' => 'Geçerli lat/lon koordinatları gönderiniz.'
            ], 400);
        }

        $merkezler = $this->locationService->findNearestMerkezler($lat, $lon, $limit);

        return response()->json([
            'success' => true,
            'data' => $merkezler,
            'user_location' => [
                'lat' => $lat,
                'lon' => $lon
            ]
        ]);
    }

    /**
     * Arama API
     */
    public function search(Request $request)
    {
        $searchTerm = $request->input('q');
        $filters = $request->input('filters', []);

        if (empty($searchTerm) && empty($filters)) {
            return response()->json([
                'error' => 'Arama parametresi gerekli',
                'message' => 'En az bir arama terimi veya filtre belirtiniz.'
            ], 400);
        }

        $results = collect();

        if (!empty($searchTerm)) {
            $results = $this->searchService->searchByAddress($searchTerm);
        } elseif (!empty($filters)) {
            $results = $this->searchService->searchByFilters($filters);
        }

        return response()->json([
            'success' => true,
            'data' => $results,
            'count' => $results->count(),
            'query' => [
                'search_term' => $searchTerm,
                'filters' => $filters
            ]
        ]);
    }
} 