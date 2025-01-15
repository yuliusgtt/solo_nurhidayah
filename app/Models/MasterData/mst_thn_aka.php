<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class mst_thn_aka extends Model
{
    protected $table = 'mst_thn_aka';

    protected $primaryKey = 'urut';

    protected $fillable = [
        'thn_aka',
    ];

    public $timestamps = false;

    public $incrementing = false;
}
