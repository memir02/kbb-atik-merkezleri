<?php

namespace App\Services;

use App\Models\AtikMerkezi;
use Illuminate\Http\Request;

/**
 * AtikMerkeziService
 * Atık merkezi işlemlerini yönetir
 */
class AtikMerkeziService
{
    /**
     * Ana sayfa için ilk merkezleri getir
     */
    public function getInitialMerkezler(int $limit = 20)
    {
        return AtikMerkezi::take($limit)->get();
    }

    /**
     * Tek merkez bilgisi getir
     */
    public function getMerkezById(int $id)
    {
        return AtikMerkezi::find($id);
    }

    /**
     * Birden fazla merkez bilgisi getir
     */
    public function getMerkezlerByIds(array $ids)
    {
        if (empty($ids)) {
            return collect();
        }
        
        return AtikMerkezi::whereIn('id', $ids)->get();
    }

    /**
     * Infinite scroll için daha fazla merkez getir
     */
    public function loadMoreMerkezler(int $offset = 0, int $limit = 20)
    {
        $merkezler = AtikMerkezi::skip($offset)->take($limit)->get();
        $totalCount = AtikMerkezi::count();
        $hasMore = $totalCount > ($offset + $limit);

        return [
            'merkezler' => $merkezler,
            'hasMore' => $hasMore,
            'total' => $totalCount
        ];
    }
} 