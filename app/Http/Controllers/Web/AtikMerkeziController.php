<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\AtikMerkeziService;
use App\Services\SearchService;
use App\Services\LocationService;
use App\Http\Requests\AtikMerkeziSearchRequest;
use Illuminate\Support\Facades\Log;

/**
 * AtikMerkeziController
 * Atık merkezi web kontrolcüsü
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
     * Ana sayfa - Arama ve filtreleme
     */
    public function index(Request $request)
    {
        error_log('============================');
        error_log('CONTROLLER: ' . date('H:i:s'));
        error_log('REQUEST: ' . json_encode($request->all()));
        error_log('QUERY STRING: ' . $request->getQueryString());
        error_log('HAS FILTER: ' . ($request->has('filter') ? 'YES' : 'NO'));
        error_log('FILTER VALUE: ' . json_encode($request->filter));
        error_log('============================');
        
        $merkezler = null;
        $tumMerkezler = null;
        $searchTerm = null;

        try {
            // Arama kontrolü
            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = $request->search;
                $merkezler = $this->searchService->searchByAddress($searchTerm);
            }
            // Filtre kontrolü - hem array hem string destekle
            elseif ($request->has('filter')) {
                $filters = $request->filter;
                
                // String ise array'e çevir
                if (is_string($filters)) {
                    $filters = [$filters];
                }
                
                if (is_array($filters) && !empty($filters)) {
                    $merkezler = $this->searchService->searchByFilters($filters);
                } else {
                    // Boş filtre durumunda da merkezler = empty collection
                    $merkezler = collect(); // Boş collection
                }
            } 
            else {
                $tumMerkezler = $this->atikMerkeziService->getInitialMerkezler(20);
            }
        } catch (\Exception $e) {
            return redirect()->route('atik-merkezleri.index')->with('error', 'Bir hata oluştu. Lütfen tekrar deneyin.');
        }
        
        return view('index', compact('merkezler', 'tumMerkezler', 'searchTerm'));
    }

    /**
     * Konuma göre en yakın atık merkezlerini getir
     */
    public function konumaGore(Request $request)
    {
        $lat = $request->input('lat');
        $lon = $request->input('lon');

        if (!$this->locationService->validateCoordinates($lat, $lon)) {
            return redirect('/')->with('error', 'Geçersiz konum bilgisi.');
        }

        $merkezler = $this->locationService->findNearestMerkezler($lat, $lon, 10);

        return view('index', [
            'merkezler' => $merkezler,
            'tumMerkezler' => null,
            'searchTerm' => 'Konumunuza en yakın atık merkezleri',
            'isLocationSearch' => true,
            'userLat' => $lat,
            'userLon' => $lon
        ]);
    }
} 