<?php

namespace App\Http\Controllers\Admin\ManualInput;

use App\Http\Controllers\Controller;
use App\Models\MasterData\mst_kelas;
use App\Models\MasterData\mst_tagihan;
use App\Models\MasterData\mst_thn_aka;
use App\Models\scctbill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EditManualController extends Controller
{
    private string $title;
    private string $mainTitle;

    public function __construct()
    {
//        $this->middleware('CheckUserRoleOrPermission');

        $this->title = 'Manual Input';
        $this->mainTitle = 'Edit Detail Post Manual';
    }

    public function index()
    {
        $data['title'] = $this->title;
        $data['mainTitle'] = $this->mainTitle;

        $data['thn_aka'] = mst_thn_aka::orderBy('thn_aka', 'desc')->get();
        $data['kelas'] = mst_kelas::orderByRaw("CASE WHEN kelas REGEXP '^[0-9]+$' THEN 0 ELSE 1 END, kelas")->get();
        $data['tagihan'] = mst_tagihan::orderBy('urut', 'asc')->get();
//        $data['v_dt_daftar_harga'] = DB::table('v_dt_daftar_harga')->get();

        return view('admin.manual_input.edit_manual', $data);
    }

    public function getTagihan(Request $request)
    {
        if (!$request->siswa) {
            return response()->json(['message' => 'Silahkan periksa form anda'], 422);
        }

        $whereAny = [
            'scctcust.nmcust',
            'scctcust.nocust',
        ];

        $select = array_unique(array_merge($whereAny, [
            'scctbill.AA',
            'scctbill.BILLNM',
            'scctbill.BILLAM',
            'scctbill.PAIDST',
            'scctbill.PAIDDT',
            'scctbill.BTA',
            'scctbill.FIDBANK',
            'scctbill.BILLCD',
            'scctbill.FUrutan',
            'scctcust.CODE02',
            'scctcust.DESC02',
            'scctbill_detail.KodePost',
            'v_dt_daftar_harga.KodeAkun',
            'v_dt_daftar_harga.NamaAkun',
        ]));

        $tagihan = scctbill::leftJoin('scctcust', 'scctcust.CUSTID', 'scctbill.CUSTID')
            ->leftJoin('scctbill_detail', function ($join) {
                $join->on('scctbill_detail.BILLCD','=','scctbill.BILLCD')
                    ->on('scctbill_detail.CUSTID','=','scctbill.CUSTID');
            })
            ->leftJoin('v_dt_daftar_harga','v_dt_daftar_harga.KodeAkun','scctbill_detail.KodePost')
            ->select($select)
            ->where('scctbill.CUSTID', $request->siswa)
            ->orderBy('scctbill.FUrutan', 'asc')
            ->groupBy('scctbill.BILLCD')
            ->get();
        return response()->json($tagihan);
    }
}
