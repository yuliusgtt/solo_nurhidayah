<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class mst_thn_aka extends Model
{
    public $timestamps = false;
    public $incrementing = false;
    protected $table = 'mst_thn_aka';
    protected $primaryKey = 'urut';
    protected $fillable = [
        'thn_aka',
    ];
}
