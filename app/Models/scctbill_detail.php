<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class scctbill_detail extends Model
{
    public $timestamps = false;
    public $incrementing = false;
    protected $table = 'scctbill_detail';
    protected $fillable = [
        'AA',
        'KodePost',
        'BILLAM',
        'CUSTID',
        'FID',
        'tahun',
        'periode',
        'BILLCD',
    ];
}
