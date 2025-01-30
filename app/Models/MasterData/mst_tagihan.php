<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class mst_tagihan extends Model
{
    public $timestamps = false;
    public $incrementing = false;
    protected $table = 'mst_tagihan';
    protected $primaryKey = 'urut';
    protected $fillable = [
        'tagihan',
        'kode'
    ];
}
