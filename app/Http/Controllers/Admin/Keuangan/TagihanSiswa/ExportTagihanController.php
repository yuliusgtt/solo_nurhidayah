<?php

namespace App\Http\Controllers\Admin\Keuangan\TagihanSiswa;

use App\Http\Controllers\Controller;
use App\Models\MasterData\mst_kelas;
use App\Models\MasterData\mst_tagihan;
use App\Models\MasterData\mst_thn_aka;
use App\Models\scctcust;
use Illuminate\Http\Request;

class ExportTagihanController extends Controller
{
    private string $title;
    private string $mainTitle;
    private string $dataTitle;

    public function __construct()
    {
        $this->title = 'Keuangan';
        $this->mainTitle = 'Tagihan Siswa';
        $this->dataTitle = 'Export Tagihan';
    }

    public function index()
    {
        $data['title'] = $this->title;
        $data['mainTitle'] = $this->mainTitle;
        $data['dataTitle'] = $this->dataTitle;


        $data['thn_aka'] = mst_thn_aka::orderBy('thn_aka', 'desc')->get();
//        dd($data['thn_aka']);
        $data['kelas'] = mst_kelas::orderByRaw("CASE WHEN kelas REGEXP '^[0-9]+$' THEN 0 ELSE 1 END, kelas")->get();
        $data['tagihan'] = mst_tagihan::orderBy('urut', 'asc')->get();

        return view('admin.keuangan.tagihan_siswa.export_tagihan.index_new', $data);
    }

    public function getSiswa(Request $request)
    {
        $kelas = $request->kelas != 'all' ? $request->kelas ?? null : null;
        $thn_aka = $request->angkatan != 'all' ? $request->angkatan ?? null : null;
//        $thn_aka = null;

        $nis = null;
        $nama = null;
        if (isset($request->cari_siswa) && $request->cari_siswa) {
            is_numeric($request->cari_siswa) ? $nis = '%' . $request->cari_siswa . '%' : $nama = '%' . $request->cari_siswa . '%';
        }
        $siswa = [];
        $kelas = mst_kelas::where('id', '=', $kelas)->first();

        $whereAny = [
            'scctcust.NMCUST as nama',
            'scctcust.NOCUST as nis',
        ];

        $select = array_unique(array_merge($whereAny, [
            'scctcust.CUSTID',
            'scctcust.NUM2ND as nomor_pendaftaran',
            'scctcust.CODE02',
            'scctcust.DESC02 as kelas',
            'scctcust.DESC03 as jenjang',
            'scctcust.DESC04 as angkatan',
        ]));

        if ($request->siswa_only == true) {
            $siswa = scctcust::when($nis, function ($query, $nis) {
                return $query->orWhere('scctcust.NOCUST', 'like', $nis)
                    ->orWhere('scctcust.NUM2ND', 'like', $nis);
            })
                ->select($select)
                ->orderBy('scctcust.NOCUST', 'asc')
                ->get()
                ->toArray();
        } else if ($kelas) {
            $siswa = scctcust::when($kelas, function ($query, $kelas) {
                return $query->where('scctcust.CODE02', '=', $kelas->unit)
                    ->where('scctcust.DESC03', '=', $kelas->kelas)
                    ->where('scctcust.DESC02', '=', $kelas->jenjang);
            })
                ->when($thn_aka, function ($query, $thn_aka) {
                    return $query->where('scctcust.DESC04', '=', $thn_aka);
                })
                ->when($nis, function ($query, $nis) {
                    return $query->where('scctcust.NOCUST', 'like', $nis);
                })
                ->when($nama, function ($query, $nama) {
                    return $query->where('scctcust.NMCUST', 'like', $nama);
                })
                ->select($select)
                ->orderBy('scctcust.NOCUST', 'asc')
                ->get()
                ->toArray();
        }

        $response = array(
            "data" => $siswa,
        );

        return response()->json($response);
    }
}
