<?php

namespace App\Http\Controllers;

use App\Models\AtikMerkezi;
use App\Models\AtikMerkeziRating;
use App\Models\AtikMerkeziFavorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    public function rate(Request $request, AtikMerkezi $atikMerkezi)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500'
        ]);

        $rating = AtikMerkeziRating::updateOrCreate(
            ['user_id' => Auth::id(), 'atik_merkezi_id' => $atikMerkezi->id],
            ['rating' => $request->rating, 'comment' => $request->comment]
        );

        // Get updated statistics
        $averageRating = $atikMerkezi->ratings()->avg('rating') ?: 0;
        $totalRatings = $atikMerkezi->ratings()->count();

        return response()->json([
            'success' => true,
            'message' => 'Puanınız kaydedildi',
            'average_rating' => round($averageRating, 1),
            'total_ratings' => $totalRatings
        ]);
    }

    /**
     * Get current user's rating for a specific center
     */
    public function getUserRating(AtikMerkezi $atikMerkezi)
    {
        $rating = AtikMerkeziRating::where([
            'user_id' => Auth::id(),
            'atik_merkezi_id' => $atikMerkezi->id
        ])->first();

        if (!$rating) {
            return response()->json([
                'success' => false,
                'message' => 'Rating bulunamadı'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'rating' => [
                'rating' => $rating->rating,
                'comment' => $rating->comment
            ]
        ]);
    }

    /**
     * Submit rating via JSON API
     */
    public function submitRating(Request $request)
    {
        $request->validate([
            'atik_merkezi_id' => 'required|exists:atik_merkezleri,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500'
        ]);

        $atikMerkezi = AtikMerkezi::findOrFail($request->atik_merkezi_id);

        $rating = AtikMerkeziRating::updateOrCreate(
            ['user_id' => Auth::id(), 'atik_merkezi_id' => $atikMerkezi->id],
            ['rating' => $request->rating, 'comment' => $request->comment]
        );

        // Get updated statistics
        $averageRating = $atikMerkezi->ratings()->avg('rating') ?: 0;
        $totalRatings = $atikMerkezi->ratings()->count();

        return response()->json([
            'success' => true,
            'message' => 'Puanınız kaydedildi',
            'average_rating' => round($averageRating, 1),
            'total_ratings' => $totalRatings
        ]);
    }

    public function addToFavorites(Request $request, AtikMerkezi $atikMerkezi)
    {
        AtikMerkeziFavorite::firstOrCreate([
            'user_id' => Auth::id(),
            'atik_merkezi_id' => $atikMerkezi->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Favorilere eklendi',
        ]);
    }

    public function removeFromFavorites(Request $request, AtikMerkezi $atikMerkezi)
    {
        AtikMerkeziFavorite::where([
            'user_id' => Auth::id(),
            'atik_merkezi_id' => $atikMerkezi->id
        ])->delete();

        return response()->json([
            'success' => true,
            'message' => 'Favorilerden çıkarıldı',
        ]);
    }
}
