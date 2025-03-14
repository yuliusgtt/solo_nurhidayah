<?php

namespace App\Http\Controllers\Admin\Keuangan;

use App\Http\Controllers\Admin\Keuangan\Saldo\SaldoVirtualAccountController;
use App\Http\Controllers\Controller;
use App\Models\scctbill;
use App\Models\scctcust;
use App\Models\sccttran;
use App\Models\ValidationMessage;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use function Laravel\Prompts\error;
use function PHPUnit\Framework\throwException;

class ManualPembayaranController extends Controller
{
    public function __construct()
    {
        $this->title = 'Keuangan';
        $this->mainTitla = 'Pembayaran Manual';
        $this->dataTitle = 'Pembayaran Manual';
        $this->showTitle = 'Detail Pembayaran Manual';


        $this->datasUrl = route('admin.keuangan.manual-pembayaran.get-data');
        $this->detailDatasUrl = '';
        $this->columnsUrl = route('admin.keuangan.manual-pembayaran.get-column');
    }

    public function getColumn()
    {
        return [
            ['data' => 'item_id', 'name' => 'no', 'className' => 'text-center', 'columnType' => 'checkbox', 'selectName' => 'tagihan[post]', 'preData' => '', 'selectClass' => 'scctbill'],
            ['data' => 'nocust', 'name' => 'NIS', 'searchable' => true, 'orderable' => true],
            ['data' => 'nmcust', 'name' => 'NAMA', 'searchable' => true, 'orderable' => true],
            ['data' => 'CODE02', 'name' => 'Unit', 'searchable' => true, 'orderable' => true],
            ['data' => 'DESC02', 'name' => 'Kelas', 'searchable' => true, 'orderable' => true],
            ['data' => 'BILLNM', 'name' => 'Nama Tagihan', 'searchable' => true, 'orderable' => true],
            ['data' => 'BILLAM', 'name' => 'Tagihan', 'searchable' => true, 'orderable' => true, 'columnType' => 'currency', 'className' => 'text-end'],
            ['data' => 'BTA', 'name' => 'Tahun AKA', 'searchable' => true, 'orderable' => true],
            ['data' => null, 'name' => 'bayar', 'columnType' => 'input', 'inputType' => 'text',
                'inputClass' => 'form-control bg-body formattedNumber',
                'inputName' => 'tagihan[nominal_bayar][]',
                'inputDisabled' => true,
                'inputPlaceholder' => 'nominal bayar',
                'excludeFromSelection' => true],
        ];
    }

    public function getData(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length");

        if ($request->siswa) {
            $columnName_arr = $request->get('columns');
            $search_arr = $request->get('search');

            $defaultColumn = 'scctbill.created_at';
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

            $tahun_pelajaran = $request->filter['tahun_pelajaran'];

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
                'scctbill.FUrutan',
                'scctcust.CODE02',
                'scctcust.DESC02',
            ]));

            $query = scctbill::leftJoin('scctcust', 'scctcust.CUSTID', 'scctbill.CUSTID')
                ->where('scctbill.CUSTID', $request->siswa)
                ->where('scctbill.PAIDST', '=', 0)
                ->where('scctbill.FSTSBolehBayar', '=', 1)
                ->when($tahun_pelajaran && $tahun_pelajaran != 'all', function ($query) use ($tahun_pelajaran) {
                    return $query->where('scctbill.BTA', '=', $tahun_pelajaran);
                })
                ->whereNull('scctbill.PAIDDT');

            $totalRecords = Cache::remember('total_tagihan_manual_bayar', 600, function () use ($query) {
                return  $query->select('count(*) as allcount')->count();
            });

            $records = $query->select($select)
                ->orderBy('FUrutan', 'asc')
                ->get()
                ->map(function ($item) {
                    $item->item_id = Crypt::encrypt($item->AA);
                    unset($item->AA);
                    return $item;
                })->toArray();
        }

        $response = array(
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords ?? 0,
            "recordsFiltered" => $totalRecords ?? 0,
            "data" => $records ?? [],
        );
        return response()->json($response);
    }

    public function index()
    {
        $data['title'] = $this->title;
        $data['mainTitle'] = $this->mainTitla;
        $data['dataTitle'] = $this->dataTitle;
        $data['showTitle'] = $this->showTitle;
        $data['columnsUrl'] = $this->columnsUrl;
        $data['thn_aka'] = \App\Models\MasterData\mst_thn_aka::select(['thn_aka'])->where('thn_aka', '!=', null)->get();

        $data['datasUrl'] = $this->datasUrl;
//        $data['thn_aka'] = mst_thn_aka::where('thn_aka', '!=', null)->get();
//        $data['kelas'] = mst_kelas::get();


        return view('admin.keuangan.manual_pembayaran', $data);
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
            'scctbill.FUrutan',
            'scctcust.CODE02',
            'scctcust.DESC02',

        ]));

        $tagihan = scctbill::leftJoin('scctcust', 'scctcust.CUSTID', 'scctbill.CUSTID')
            ->where('scctbill.PAIDST', 0)
            ->select($select)
            ->where('scctbill.CUSTID', $request->siswa)
            ->orderBy('scctbill.FUrutan', 'asc')
            ->get();
        return response()->json($tagihan);
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'tanggal' => ['required','regex:/^\d{2}-\d{2}-\d{4}$/'],
                'siswa' => ['required'],
                'bank' => ['required', 'in:1140000,1140001,1140002,1140003,1140004,1140005,1200001,1200002'],
                'tagihan.post' => ['required','array','min:1'],
                'tagihan.post.*' => ['required'],
                'tagihan.nominal_bayar' => ['required','array','min:1'],
                'tagihan.nominal_bayar.*' => ['required','regex:/^[0-9]+(\.[0-9]{3})*$/']

            ],
            ValidationMessage::messages(),
            ValidationMessage::attributes()
        );

        if ($validator->fails()) {
            if ($validator->errors()->has('tagihan.nominal_bayar.*') || $validator->errors()->has('tagihan.post.*')) {
                return response()->json(['message' => 'Silahkan cek tagihan yang anda pilih,<br> pastikan telah mengisi nominal pembayaran'], 422);
            } else {
                return response()->json(['message' => $validator->errors()->first(), 'error' => $validator->errors()], 422);
            }
        }

        $posts = [];
        foreach ($request->tagihan['post'] as $key => $encryptedValue) {
            try {
                $posts[$key] = Crypt::decrypt($encryptedValue);
            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                return response()->json(['message' => 'Silahkan cek tagihan yang anda pilih,<br> pastikan telah mengisi nominal pembayaran'], 422);
            }
        }

        $nominalBayar = [];
        $totalBayar = 0;
        foreach ($request->tagihan['nominal_bayar'] as $key => $value) {
            $nominalBayar[$key] = str_replace('.', '', $value);
            $totalBayar += $nominalBayar[$key];
        }

        $siswa = scctcust::where('CUSTID', $request->siswa)->first();
        if (!$siswa) return response()->json(['message' => 'Siswa tidak ditemukan'], 422);

        $tagihans = scctbill::whereIn('AA', $posts)
            ->where('PAIDST', '=', 0)
            ->where('FSTSBolehBayar', '=', 1)
            ->orderBy('FUrutan', 'asc')
            ->get();

        $queriedIds = $tagihans->pluck('AA')->toArray();
        $missingIds = array_diff($posts, $queriedIds);
        if (!empty($missingIds)) {
            return response()->json([
                'error' => 'Tagihan tidak ditemukan, silahkan coba tekan tombol cari untuk memuat ulang tagihan yang ada',
            ], 422);
        }
        $tagihanForPrint = [];
        $message = 'Tagihan sukses dibayar. <br> Total Bayar : Rp. '.number_format($totalBayar,0,',','.').'.<br> Apakah anda ingin mencetak pembayaran tagihan?';

        $dateInput = $request->input('tanggal');
        $formattedDate = Carbon::createFromFormat('d-m-Y', $dateInput)->toDateTimeString();
        try {
            DB::beginTransaction();

            if ($request->bank == '1140002') {
                $newRequest = new Request(['siswa' => $request->siswa]);
                $saldoController = new SaldoVirtualAccountController();
                $saldo = $saldoController->getSaldo($newRequest);
                if ($saldo < $totalBayar) return response()->json(['message' => 'Saldo siswa kurang.<br> saldo: Rp.' . $saldo], 422);
                $sisaSaldo = $saldo - $totalBayar;
                $message = 'Tagihan sukses dibayar.
                                <br> Total Bayar: Rp. '.number_format($totalBayar,0,',','.').'.
                                <br> Sisa saldo: Rp. '.number_format($sisaSaldo,0,',','.').'.
                                <br> apakah anda ingin mencetak pembayaran tagihan?';
            }

            foreach ($tagihans as $item) {
                $tagihanForPrint[] = $item->id;
                $keyForSearch = array_search($item->id, $posts);
                $nominal = $nominalBayar[$keyForSearch];
                $oldBill = $item->BILLAM;

                if($nominal == 0)  return response()->json(['message' => 'Nominal Pembayaran untuk tagihan terlalu besar!'], 422);
                if ($item->cicil == 0 && $item->BILLAM > $nominal) return response()->json(['message' => 'Nominal Pembayaran Kurang !'], 422);
                if($oldBill < $nominal)  return response()->json(['message' => 'Nominal Pembayaran untuk tagihan terlalu besar!'], 422);

                $item->update([
                    'PAIDST' => 1,
                    'PAIDDT' => $formattedDate,
                    'PAIDDT_ACTUAL' => date('Y-m-d H:i:s'),
                    'FIDBANK' => $request->input('bank'),
                    'PAIDAM' => $item->BILLAM
                ]);

                $metode = 'FROM SALDO';
                if ($request->bank == '1140002') {
                    sccttran::create([
                        'CUSTID' => $siswa->id,
                        'METODE' => $metode,
                        'TRXDATE' => now(),
                        'DEBET' => $nominal,
                    ]);
                }
            }
            $request->session()->put('key', 'value');

            $request->session()->forget('siswa_tagihan_baru_dibayar');
            $request->session()->forget('tagihan_baru_dibayar');
            session(['siswa_tagihan_baru_dibayar' => $siswa]);
            session(['tagihan_baru_dibayar' => $tagihanForPrint]);
            DB::commit();
            return response()->json(['message' => $message], 200);
        } catch (QueryException $e) {
            DB::rollBack();
            return response()->json(['message' => 'Tagihan gagal dibayar', 'error' => $e], 422);
        }
    }

    public function cetakPembayaran(Request $request)
    {
        if ($request->session()->has('tagihan_baru_dibayar')) {
            $tagihanForPrint = $request->session()->get('tagihan_baru_dibayar');
            $nis = $request->session()->get('siswa_tagihan_baru_dibayar')->nis;
            $siswa = mst_siswa::where('nis', $nis)
                ->leftJoin('mst_kelas', 'mst_kelas.id', '=', 'mst_siswas.id_kelas')
                ->leftJoin('mst_thn_aka', 'mst_thn_aka.id', '=', 'mst_siswas.id_thn_aka')
                ->select([
                    'mst_siswas.no_pendaftaran',
                    'mst_siswas.nis',
                    'mst_siswas.nisn',
                    'mst_siswas.nama',
                    'mst_siswas.agama',
                    'mst_siswas.tmp_lahir',
                    'mst_siswas.tgl_lahir',
                    'mst_siswas.jk',
                    'mst_siswas.id_kelas',
                    'mst_siswas.id_thn_aka',
                    'mst_siswas.angkatan',
                    'mst_siswas.alamat',
                    'mst_siswas.nowa',
                    'mst_siswas.email',
                    'mst_siswas.nama_ortu',
                    'mst_kelas.kelas as kelas',
                    'mst_kelas.kelompok as kelompok',
                    'mst_thn_aka.thn_aka as thn_aka',
                ])->first();

            $tagihans = scctbill::select([
                'CUSTID',
                'BILLCD',
                'BILLAC',
                'BILLNM',
                'BILLAM',
                'BILL_TOTAL',
                'id_group',
                'FLPART',
                'PAIDAM',
                'PAIDST',
                'PAIDDT',
                'NOREFF',
                'FSTSBolehBayar',
                'FUrutan',
                'FTGLTagihan',
                'FIDBANK',
                'FID',
                'FRecID',
                'AA',
                'BTA',
                'TRANSNO',
                'CreateID',
                'PAIDDT_ACTUAL',
                'tahun',
                'periode',
                'KodePost',
                'siap_bayar',
                'cicil',
            ])->selectRaw('getSisaTagihan(id_group) as sisa_tagihan')->whereIn('id', $tagihanForPrint)->get();
            //            dd($tagihans);
            //            $taihanCicil = scctbill::whereIn('id_group', $tagihanForPrint)->where('cicil',1)->get();
            //            dd($tagihanForPrint);

            //            DB::raw('COALESCE(SUM(transaksi_simpanan_sukarelas.debet), 0) - COALESCE(SUM(transaksi_simpanan_sukarelas.kredit), 0) as simpanan');
            $pdf = Pdf::loadView('pdf.kuitansi_with_2000', ['tagihans' => $tagihans, 'siswa' => $siswa]);
            return $pdf->download('bukti-pembayaran - ' . $siswa->nama . ' - ' . $siswa->nis . '.pdf');
        } else {
            return response()->json(['message' => 'Silakhan Lakukan pembayaran terlebih dahulu'], 422);
        }
    }

    public function cetakTagihan(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'tanggal' => ['required'],
                'siswa' => ['required'],
//                'bank' => ['required', 'in:1140000,1140001,1140002,1140003,1140004,1140005,1200001,1200002'],
                'tagihan.post' => ['required','array','min:1'],
                'tagihan.post.*' => ['required'],
            ],
            ValidationMessage::messages(),
            ValidationMessage::attributes()
        );
        if ($validator->fails()) return response()->json(['message' => 'Silahkan periksa form anda', 'error' => $validator->errors()], 422);



        $whereAny = [
            'scctcust.NMCUST',
            'scctcust.NOCUST',
            'scctcust.NUM2ND',
        ];

        $select = array_unique(array_merge($whereAny, [
            'scctcust.CODE02',
            'scctcust.DESC02',
            'scctcust.DESC03',
            'scctcust.DESC04',

        ]));

        $posts = [];
        foreach ($request->tagihan['post'] as $key => $encryptedValue) {
            try {
                $posts[$key] = Crypt::decrypt($encryptedValue);
            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                return response()->json(['message' => 'Silahkan cek tagihan yang anda pilih'], 422);
            }
        }

        $siswa = scctcust::where('scctcust.CUSTID', $request->siswa)
            ->select($select)
            ->first();

        if (!$siswa) return response()->json(['message' => 'Siswa tidak ditemukan'], 422);
        try {
            $tagihans = scctbill::leftJoin('scctcust', 'scctcust.CUSTID', 'scctbill.CUSTID')
                ->where('scctbill.CUSTID', $request->siswa)
                ->whereIn('scctbill.AA', $posts)
                ->select(['scctbill.AA',
                    'scctbill.BILLNM',
                    'scctbill.BILLAM',
                    'scctbill.BILLAC',
                    'scctbill.PAIDST',
                    'scctbill.PAIDDT',
                    'scctbill.BTA',
                    'scctbill.FIDBANK',
                    'scctbill.FUrutan',])
                ->get();

            if (!$tagihans) return response()->json(['message' => 'Tagihan Tidak Ditemukan'], 422);
            $pdf = Pdf::loadView('export.tagihan_manual', ['tagihans' => $tagihans, 'siswa' => $siswa]);
            return $pdf->download('tagihan-siswa.pdf');
        } catch (Exception $e) {
            return response()->json(['message' => 'Tagihan Tidak Ditemukan'], 422);
        }
    }
}
