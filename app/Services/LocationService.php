<?php

namespace App\Services;

use App\Models\AtikMerkezi;
use Illuminate\Support\Collection;

/**
 * Location Service
 * Konum işlemlerini yönetir
 */
class LocationService
{
    /**
     * Konuma göre en yakın atık merkezlerini getir
     */
    public function findNearestMerkezler(float $lat, float $lon, int $limit = 10): Collection
    {
        // En yakın merkezleri bul (Haversine formülü ile)
        return AtikMerkezi::whereNotNull('lat')
            ->whereNotNull('lon')
            ->selectRaw("
                *,
                ROUND(
                    (6371 * acos(
                        cos(radians(?)) * 
                        cos(radians(lat)) * 
                        cos(radians(lon) - radians(?)) + 
                        sin(radians(?)) * 
                        sin(radians(lat))
                    )), 2
                ) AS distance
            ", [$lat, $lon, $lat])
            ->orderBy('distance')
            ->take($limit)
            ->get();
    }

    /**
     * Koordinatları validate et
     */
    public function validateCoordinates(?float $lat, ?float $lon): bool
    {
        return !is_null($lat) && 
               !is_null($lon) && 
               $lat >= -90 && $lat <= 90 && 
               $lon >= -180 && $lon <= 180;
    }

    /**
     * İki nokta arasındaki mesafeyi hesapla (km cinsinden)
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * 
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }
} 