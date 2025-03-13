<?php

namespace App\Http\Controllers\Admin\Keuangan\TagihanSiswa;

use App\Http\Controllers\Controller;
use App\Models\MasterData\mst_kelas;
use App\Models\MasterData\mst_tagihan;
use App\Models\MasterData\mst_thn_aka;
use App\Models\MasterData\u_akun;
use App\Models\MasterData\u_daftar_harga;
use App\Models\scctbill;
use App\Models\scctcust;
use App\Models\ValidationMessage;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use function Laravel\Prompts\error;

class BuatTagihanController extends Controller
{
    public function __construct()
    {
//        $this->middleware('CheckUserRoleOrPermission');

        $this->title = 'Keuangan';
        $this->mainTitle = 'Tagihan Siswa';
        $this->dataTitle = 'Buat Tagihan';
        $this->showTitle = 'Detail Buat Tagihan';


//        $this->datasUrl = route('admin.users.get-users-data');
        $this->detailDatasUrl = '';
//        $this->columnsUrl = route('admin.users.get-users-column');
    }

    public function index()
    {
        $data['title'] = $this->title;
        $data['mainTitle'] = $this->mainTitle;
        $data['dataTitle'] = $this->dataTitle;
        $data['showTitle'] = $this->showTitle;


        $data['thn_aka'] = mst_thn_aka::orderBy('thn_aka', 'desc')->get();
//        dd($data['thn_aka']);
        $data['kelas'] = mst_kelas::orderByRaw("CASE WHEN kelas REGEXP '^[0-9]+$' THEN 0 ELSE 1 END, kelas")->get();
        $data['tagihan'] = mst_tagihan::orderBy('urut', 'asc')->get();

        return view('admin.keuangan.tagihan_siswa.buat_tagihan.index_new', $data);
    }

    public function getColumn()
    {
        return [
            [
                'data' => 'check',
                'name' => "<input type='checkbox' class='form-check-input' id='check-all'>",
                'className' => 'text-center',
                'input' => 'check',
                "targets" => 0
            ],
            ['data' => 'kode', 'name' => 'Kode', 'searchable' => true, 'orderable' => true],
            ['data' => 'nama_post', 'name' => 'Nama Post', 'searchable' => true, 'orderable' => true],
            ['data' => 'nama_tagihan', 'name' => 'Nama Tagihan', 'searchable' => true, 'orderable' => true],
            ['data' => 'nominal', 'name' => 'Nominal', 'input' => 'text', 'currency' => true],
            ['data' => 'potongan', 'name' => 'Potongan', 'input' => 'text', 'currency' => true],
            ['data' => 'cicilan', 'name' => 'Cicilan', 'input' => 'text', 'currency' => false],
        ];
    }

    public function getData(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length");

        $columnName_arr = $request->get('columns');
        $search_arr = $request->get('search');

        $defaultColumn = 'created_at';
        $defaultOrder = 'desc';

        if ($request->has('order')) {
            $columnIndex_arr = $request->get('order');
            $columnIndex = $columnIndex_arr[0]['column'];
            $columnSortOrder = $columnIndex_arr[0]['dir'];
        } else {
            $columnIndex = $defaultColumn;
            $columnSortOrder = $defaultOrder;
        }

        $columnName = $columnName_arr[$columnIndex]['data'];
        $searchValue = $search_arr['value'];

        if (!$columnName || $columnName == 'no') {
            $columnName = $defaultColumn;
            $columnSortOrder = $defaultOrder;
        }

        // Total records
        $totalRecords = mst_post::select('count(*) as allcount')->count();
        $totalRecordswithFilter = mst_post::select('count(*) as allcount')
            ->count();

        $records = mst_post::orderBy($columnName, $columnSortOrder)
            ->select('*')
            ->get()
            ->map(function ($item) {
                $item->item_id = Crypt::encrypt($item->id);
                unset($item->id);
                return $item;
            });

        $data_arr = array();
        $numberStart = $start + 1;
        foreach ($records as $record) {
            $data_arr[] = array(
                'item_id' => $record->item_id,
                "no" => $numberStart,
                'kode' => $record->kode,
                'id_thn_aka' => $record->id_thn_aka,
                'nama_post' => $record->nama_post,
                'nama_tagihan' => $record->nama_post,
                'nomor_rekening' => $record->nomor_rekening,
                'nominal' => $record->nominal,
                'edit' => true,
                'delete' => true,
            );

            $numberStart++;
        }
        $response = array(
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecordswithFilter,
            "data" => $data_arr,
        );
        return response()->json($response);
    }

    public function getColumnSiswa()
    {
    }

    public function getDataSiswa(Request $request)
    {
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

        if ($kelas) {
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

    public function getMasterHarga(Request $request)
    {
        $data = [];
        $thn_aka = $request->thn_aka != 'all' ? $request->thn_aka ?? null : null;
        $kelas = $request->kelas != 'all' ? $request->kelas ?? null : null;

        $select = [
            'u_daftar_harga.thn_masuk as tahun_masuk',
            'u_daftar_harga.KodeAkun as kode_akun',
            'u_akun.NamaAkun as nama_akun',
            'u_daftar_harga.nominal as nominal',
        ];

        if ($thn_aka) {
            $data = u_akun::orderBy('u_akun.KodeAkun', 'asc')
                ->join('u_daftar_harga', 'u_daftar_harga.KodeAkun', '=', 'u_akun.KodeAkun')
                ->when($thn_aka, function ($query, $thn_aka) {
                    return $query->where('u_daftar_harga.thn_masuk', 'like', $thn_aka);
                })->when($kelas, function ($query, $kelas) {
                    return $query->where(function ($q) use ($kelas) {
                        $q->where('u_daftar_harga.kode_prod', 'like', $kelas)
                            ->orWhereNull('u_daftar_harga.kode_prod')
                            ->orWhere('u_daftar_harga.kode_prod', '=', '');
                    });
                })
                ->select($select)
                ->orderBy('u_daftar_harga.KodeAkun', 'asc')
                ->whereNotNull('u_daftar_harga.KodeAkun')
                ->get()
                ->toArray();
        }

        $response = array(
            "data" => $data,
        );

        return response()->json($response);
    }


    public function store(Request $request)
    {
        $request->validate([
            'tahun_pelajaran' => ['required', 'regex:/^\d{4}\/\d{4}(?:\s*-\s*(GANJIL|GENAP))?$/'],
            'tahun_angkatan' => ['required', 'regex:/^\d{4}\/\d{4}(?:\s*-\s*(GANJIL|GENAP))?$/'],
            'kelas' => ['required'],
            'fungsi' => ['required', 'regex:/^\d{6}$/'],
            'siswa' => ['required', 'array', 'min:1'],
            'tagihan' => ['required', 'array', 'min:1'],
            'tagihan.*.tagihan' => ['required'],
            'tagihan.*.nominal' => ['required', 'regex:/^[0-9]+(\.[0-9]{3})*$/', 'not_in:0'],
        ], ValidationMessage::messages(), ValidationMessage::attributes());

        $tahun_akademik = mst_thn_aka::where('thn_aka', $request->tahun_pelajaran)->value('thn_aka');

        if (!$tahun_akademik || !preg_match('/\d{4}\/\d{4}/', $tahun_akademik, $matches)) {
            return response()->json(['message' => 'Tahun akademik tidak valid'], 422);
        }

        $tahun_aka = $matches[0];

        $tahun_pelajaran = $request->input('tahun_pelajaran');
        $kelas = $request->input('kelas');
        $tagihans = u_daftar_harga::leftJoin('u_akun', 'u_akun.KodeAkun', '=', 'u_daftar_harga.KodeAkun')
            ->whereIn('u_daftar_harga.KodeAkun', $request->input('tagihan.*.tagihan'))
            ->when($tahun_pelajaran, function ($query, $tahun_pelajaran) {
                return $query->where('u_daftar_harga.thn_masuk', 'like', $tahun_pelajaran);
            })->when($kelas, function ($query, $kelas) {
                return $query->where(function ($q) use ($kelas) {
                    $q->where('u_daftar_harga.kode_prod', 'like', $kelas)
                        ->orWhereNull('u_daftar_harga.kode_prod')
                        ->orWhere('u_daftar_harga.kode_prod', '=', '');
                });
            })->get();

        if ($tagihans->isEmpty()) return response()->json(['message' => 'Tagihan tidak ditemukan'], 422);
        if (count($request->input('tagihan')) != $tagihans->count()) return response()->json(['message' => 'Jumlah tagihan yang dipilih tidak sesuai dengan jumlah data, silahkan muat ulang halaman!'], 422);

        try {
            DB::beginTransaction();

            $siswas = scctcust::whereIn('CUSTID', $request->input('siswa'))->get();
            if ($siswas->isEmpty()) return response()->json(['message' => 'Siswa tidak ditemukan'], 422);
            if (count($request->input('siswa')) != $siswas->count()) return response()->json(['message' => 'Jumlah siswa yang dipilih tidak sesuai dengan jumlah data, silahkan muat ulang halaman!'], 422);

            foreach ($siswas as $siswa) {
                foreach ($request->input('tagihan') as $item) {
                    if (isset($item['post']) && $item['nama_tagihan'] >= 0 && $item['tagihan']) {
                        $nominal = str_replace('.', '', $item['tagihan']);
                        if (!is_numeric($nominal)) {
                            return response()->json(['message' => 'Nominal tidak boleh kosong'], 422);
                        }
                        $post = mst_post::where('kode', $item['post'])->first();
                        if (!$post) {
                            return response()->json(['message' => 'Post tidak ditemukan'], 422);
                        }
                        $tagihanSiswaTerbaru = scctbill::where('CUSTID', $siswa->id)
                            ->orderBy('FUrutan', 'DESC')
                            ->first();

                        $cicilan = $item['jenis'] == 'cicilan' ? 1 : 0;
                        $urut = $tagihanSiswaTerbaru ? $tagihanSiswaTerbaru['FUrutan'] + 1 : 1;
                        $bill = scctbill::create([
                            'CUSTID' => $siswa->id,
                            'BILLAC' => $item['periode_tahun'] . $item['periode_bulan'],
                            'BILLNM' => $item['nama_tagihan'],
                            'KodePost' => $item['post'],
                            'BILLAM' => $nominal,
                            'BILL_TOTAL' => $nominal,
                            'PAIDST' => 0,
                            'FUrutan' => $urut,
                            'FTGLTagihan' => now(),
                            'BTA' => $tahun_akademik,
                            'cicil' => $cicilan,
                        ]);

                        $bill->AA = $bill->id;
                        $bill->BILLCD = date('Ymd') . '-' . $bill->id;
                        $bill->id_group = $bill->id;
                        $bill->save();
                    }
                }
            }
            DB::commit();
            return response()->json(['message' => 'Tagihan telah dibuat']);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Data gagal dibuat', 'error' => $e], 422);
        }
    }
}
