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
    ];

    // JSON serialization'da getter attribute'ları dahil et
    protected $appends = ['border_class'];

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
