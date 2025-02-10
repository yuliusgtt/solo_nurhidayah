<?php

namespace App\Http\Controllers\Admin\Keuangan\TagihanSiswa;

use App\Http\Controllers\Controller;
use App\Models\MasterData\mst_kelas;
use App\Models\MasterData\mst_tagihan;
use App\Models\MasterData\mst_thn_aka;
use App\Models\MasterData\u_akun;
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
        $data['jenjang'] = mst_kelas::select('jenjang')->distinct()
            ->orderByRaw("CASE WHEN jenjang REGEXP '^[0-9]+$' THEN 0 ELSE 1 END, jenjang")->get();
        $data['kelas'] = mst_kelas::select('kelas')->distinct('kelas')
            ->orderByRaw("CASE WHEN kelas REGEXP '^[0-9]+$' THEN 0 ELSE 1 END, kelas")->get();
        $data['tagihan'] = u_akun::orderBy('KodeAkun', 'asc')->get();

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
        $jenjang = $request->jenjang != 'all' ? $request->jenjang ?? null : null;
        $thn_aka = $request->angkatan != 'all' ? $request->angkatan ?? null : null;

        $nis = null;
        $nama = null;
        if (isset($request->cari_siswa) && $request->cari_siswa) {
            is_numeric($request->cari_siswa) ? $nis = '%' . $request->cari_siswa . '%' : $nama = '%' . $request->cari_siswa . '%';
        }

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

//        dd($jenjang, $kelas, $thn_aka, $nis, $nama);


        $siswa = scctcust::when($jenjang, function ($query, $jenjang) {
            return $query->where('scctcust.DESC02', 'like', $jenjang);
        })
            ->when($kelas, function ($query, $kelas) {
                return $query->where('scctcust.DESC03', 'like', $kelas);
            })
            ->when($thn_aka, function ($query, $thn_aka) {
                return $query->where('scctcust.DESC04', 'like', $thn_aka);
            })
            ->when($nis, function ($query, $nis) {
                return $query->where('scctcust.NOCUST', 'like', $nis);
            })
            ->when($nama, function ($query, $nama) {
                return $query->where('scctcust.NMCUST', 'like', $nama);
            })
            ->select($select)
            ->orderBy('scctcust.NOCUST','asc')
            ->get()
            ->toArray();

        $response = array(
            "data" => $siswa,
        );

        return response()->json($response);
    }

    public function getMasterHarga(Request $request)
    {
        $data = [];
        $thn_aka = $request->thn_aka != 'all' ? $request->thn_aka ?? null : null;

        $select =  [
            'u_daftar_harga.thn_masuk as tahun_masuk',
            'u_daftar_harga.KodeAkun as kode_akun',
            'u_akun.NamaAkun as nama_akun',
            'u_daftar_harga.nominal as nominal',
        ];

        if ($thn_aka) {
            $data =  u_akun::orderBy('u_akun.KodeAkun', 'asc')
                    ->join('u_daftar_harga', 'u_daftar_harga.KodeAkun', '=', 'u_akun.KodeAkun')
                ->when($thn_aka, function ($query, $thn_aka) {
                    return $query->where('u_daftar_harga.thn_masuk', 'like', $thn_aka);
                })
                ->whereNotNull('u_akun.KodeAkun')
                ->select($select)
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
            'per' => ['required'],
            'id_thn_aka' => ['required'],
            'tagihan' => ['required'],
            'tagihan.*.jenis' => ['required', 'in:satuan,cicilan'],
            'tagihan.*.periode_bulan' => ['required', 'in:01,02,03,04,05,06,07,08,09,10,11,12'],
            'tagihan.*.periode_tahun' => ['required', 'regex:/^\d{4}$/'],
        ], ValidationMessage::messages(), ValidationMessage::attributes());

        $per = $request->input('per');
        $tagihan = $request->input('tagihan');
        $tahun_akademik = mst_thn_aka::where('id', $request->id_thn_aka)->value('thn_aka');
        if (!$tahun_akademik) {
            return response()->json(['message' => 'Tahun akademik tidak valid'], 422);
        }
        $kelas = $request->kelas != 'all' ? $request->kelas ?? null : null;
        $angkatan = $request->id_angkatan != 'all' ? $request->id_angkatan ?? null : null;
        try {
            DB::beginTransaction();
            switch ($per) {
                case 'id_angkatan':
                    $siswas = mst_siswa::select('id', 'nama', 'nis')->when($angkatan, function ($query, $angkatan) {
                        return $query->where('id_thn_aka', 'like', $angkatan);
                    })->get();
                    if ($siswas->isEmpty()) return response()->json(['message' => 'Tidak ada siswa di angkatan ini'], 422);

                    break;
                case 'siswa':
                case 'kelas':
                    $siswas = mst_siswa::select('id', 'nama', 'nis')->whereIn('nis', $request->input('siswa'))->get();
                    if ($siswas->isEmpty()) return response()->json(['message' => 'Siswa tidak ditemukan'], 422);
                    if (count($request->input('siswa')) != $siswas->count()) return response()->json(['message' => 'Jumlah siswa yang dipilih tidak sesuai dengan jumlah data, silahkan muat ulang halaman!'], 422);
                    break;
                default:
                    return response()->json(['message' => 'Data tidak valid, silahkan muat ulang halaman '], 422);
            }

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
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Data gagal dibuat', 'error' => $e], 422);
        }
    }
}
