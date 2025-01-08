<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class mst_tagihan extends Model
{
    protected $table = 'mst_tagihan';

    protected $primaryKey = 'urut';

    public $timestamps = false;

    public $incrementing = false;
}
