<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class u_daftar_harga extends Model
{
    public $timestamps = false;
    public $incrementing = false;
    protected $table = 'u_daftar_harga';
    protected $primaryKey = 'urut';
    protected $fillable = [
        'kode_fak',
        'kode_prod',
        'KodeAkun',
        'NamaAkun',
        'thn_masuk',
        'nominal',
        'NoRek'
    ];
}
