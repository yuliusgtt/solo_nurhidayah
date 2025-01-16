<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class mst_sekolah extends Model
{
    public $timestamps = false;
    public $incrementing = false;
    protected $table = 'mst_sekolah';
    protected $primaryKey = 'urut';
}
