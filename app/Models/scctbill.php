<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class scctbill extends Model
{
    public $timestamps = false;
    protected $table = 'scctbill';
    protected $primaryKey = 'AA';
    protected $fillable = [
        'CUSTID',
        'BILLCD',
        'BILLAC',
        'BILLNM',
        'BILLAM',
        'FLPART',
        'PAIDST',
        'PAIDDT',
        'NOREFF',
        'FSTSBolehBayar',
        'FUrutan',
        'FTGLTagihan',
        'FIDBANK',
        'FRecID',
        'AA',
        'BTA',
        'BILLTOT',
        'TRANSNO',
        'BAYAR'
    ];
}
