<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtikMerkeziRating extends Model
{
    protected $fillable = ['user_id', 'atik_merkezi_id', 'rating', 'comment'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function atikMerkezi()
    {
        return $this->belongsTo(AtikMerkezi::class);
    }
}
