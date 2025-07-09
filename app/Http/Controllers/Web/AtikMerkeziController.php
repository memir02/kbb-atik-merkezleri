<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\AtikMerkeziService;
use App\Services\SearchService;
use App\Services\LocationService;

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
        $merkezler = null;
        $tumMerkezler = null;
        $searchTerm = null;

        // Arama kontrolü
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $merkezler = $this->searchService->searchByAddress($searchTerm);
        }
        // Filtre kontrolü
        elseif ($request->has('filter') && is_array($request->filter)) {
            $merkezler = $this->searchService->searchByFilters($request->filter);
        } 
        else {
            // Ana sayfa için ilk merkezleri getir
            $tumMerkezler = $this->atikMerkeziService->getInitialMerkezler(20);
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