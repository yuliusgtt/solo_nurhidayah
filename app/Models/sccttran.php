<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class sccttran extends Model
{
    public $timestamps = false;
    public $incrementing = false;
    protected $table = 'sccttran';
    protected $primaryKey = 'urut';

    protected $fillable = [
        'CUSTID',
        'METODE',
        'TRXDATE',
        'NOREFF',
        'FIDBANK',
        'KDCHANNEL',
        'DEBET',
        'KREDIT',
        'REFFBANK',
        'TRANSNO',
    ];
}
