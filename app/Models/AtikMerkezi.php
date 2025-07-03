<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtikMerkezi extends Model
{
    // Tablo adı (varsayılan model adı kebab-case değilse belirt)
    protected $table = 'atik_merkezleri';

    // Toplu atama yapılacak sütunlar
    protected $fillable = [
        'title',
        'content',
        'lat',
        'lon',
    ];

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

}
