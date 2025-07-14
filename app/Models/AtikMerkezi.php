<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * AtikMerkezi
 * Atık merkezi modeli
 */
class AtikMerkezi extends Model
{
    // Tablo adı 
    protected $table = 'atik_merkezleri';

    // Toplu atama yapılacak sütunlar
    protected $fillable = [
        'title',
        'content',
        'lat',
        'lon',
        'adres',
    ];

    // JSON serialization'da getter attribute'ları dahil et
    protected $appends = ['border_class', 'average_rating', 'total_ratings', 'star_rating_html'];

    /**
     * Ratings relationship
     */
    public function ratings()
    {
        return $this->hasMany(AtikMerkeziRating::class);
    }

    /**
     * Favorites relationship
     */
    public function favorites()
    {
        return $this->hasMany(AtikMerkeziFavorite::class);
    }

    /**
     * Average rating getter
     */
    public function getAverageRatingAttribute()
    {
        return $this->ratings()->avg('rating') ?: 0;
    }

    /**
     * Total ratings count getter
     */
    public function getTotalRatingsAttribute()
    {
        return $this->ratings()->count();
    }

    /**
     * Star rating HTML getter
     */
    public function getStarRatingHtmlAttribute()
    {
        $average = $this->average_rating;
        $total = $this->total_ratings;
        
        if ($total == 0) {
            return '<small class="text-muted"><i class="fas fa-star text-muted me-1"></i>Henüz değerlendirilmemiş</small>';
        }

        $stars = '';
        $fullStars = floor($average);
        $hasHalfStar = ($average - $fullStars) >= 0.5;

        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $fullStars) {
                $stars .= '<i class="fas fa-star text-warning" style="font-size: 0.9rem;"></i>';
            } elseif ($i == $fullStars + 1 && $hasHalfStar) {
                $stars .= '<i class="fas fa-star-half-alt text-warning" style="font-size: 0.9rem;"></i>';
            } else {
                $stars .= '<i class="far fa-star text-muted" style="font-size: 0.9rem;"></i>';
            }
        }

        return $stars . ' <small class="text-muted ms-1">(' . number_format($average, 1) . ' - ' . $total . ' değerlendirme)</small>';
    }

    /**
     * content alanı set edilirken HTML etiketlerini kaldır.
     *
     * @param  string|null  $value
     * @return void
     */
    public function setContentAttribute($value)
{
    $clean = strip_tags($value);              // <p> gibi HTML etiketlerini sil
    $decoded = html_entity_decode($clean);    // HTML entity’leri gerçek karaktere dönüştür
    $this->attributes['content'] = $decoded;
}
public function getBorderClassAttribute()
{
    $content = mb_strtoupper($this->content, 'UTF-8'); // Türkçe karakterler için mb_strtoupper
    $borderClass = 'border-secondary'; // varsayılan gri çerçeve

    if (str_contains($content, '1. SINIF ATIK GETİRME MERKEZİ')) {
        $borderClass = 'border-success';
    } elseif (str_contains($content, 'MOBİL ATIK GETİRME MERKEZİ')) {
        $borderClass = 'border-primary';
    } elseif (str_contains($content, 'BİTKİSEL ATIK YAĞ')) {
        $borderClass = 'border-warning';
    } elseif (str_contains($content, 'ATIK CAM')) {
        $borderClass = 'border-info';
    } elseif (str_contains($content, 'TEKSTİL KUMBARASI')) {
        $borderClass = 'border-success';
    } elseif (str_contains($content, 'ATIK GEÇİCİ DEPOLAMA ÜNİTESİ')) {
        $borderClass = 'border-secondary';
    } elseif (str_contains($content, 'ATIK İLAÇ')) {
        $borderClass = 'border-danger';
    } elseif (str_contains($content, 'İNERT ATIK')) {
        $borderClass = 'border-secondary';
    } elseif (str_contains($content, 'HAFRİYAT')) {
        $borderClass = 'border-dark';
    }

    return $borderClass;
}
}
