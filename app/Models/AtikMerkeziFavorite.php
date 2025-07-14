<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtikMerkeziFavorite extends Model
{
    protected $fillable = ['user_id', 'atik_merkezi_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function atikMerkezi()
    {
        return $this->belongsTo(AtikMerkezi::class);
    }
}
