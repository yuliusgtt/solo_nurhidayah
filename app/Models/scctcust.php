<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class scctcust extends Model
{
    public $timestamps = false;
    public $incrementing = false;
    protected $table = 'scctcust';
    protected $primaryKey = 'CUSTID';

    protected $fillable = [
        'CUSTID',
        'NOCUST',
        'NMCUST',
        'NUM2ND',
        'STCUST',
        'CODE01',
        'DESC01',
        'CODE02',
        'DESC02',
        'CODE03',
        'DESC03',
        'CODE04',
        'DESC04',
        'CODE05',
        'DESC05',
        'TOTPAY',
        'GENUS',
        'LastUpdate',
        'GetWisma',
        'GENUSContact',
    ];

    public static function showVA($nis): string
    {
        $prefix = config('app.nova');
        if (strlen($nis) >= 10) {
            $nova = $nis;
        } else {
            $nova = str_pad($nis, 10, '0', STR_PAD_LEFT);
        }
        return $prefix . $nova;
    }
}
