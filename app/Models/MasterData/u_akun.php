<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class u_akun extends Model
{
    public $timestamps = false;
    public $incrementing = false;
    protected $table = 'u_akun';
    protected $primaryKey = 'KodeAkun';
    protected $fillable = [
        'KodeAkun',
        'NamaAkun',
        'NoRek'
    ];
}
