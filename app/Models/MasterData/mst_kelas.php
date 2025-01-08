<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class mst_kelas extends Model
{
    protected $table = 'mst_kelas';

    protected $primaryKey = 'urut';

    public $timestamps = false;

    public $incrementing = false;
}
