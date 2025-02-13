<?php

namespace App\Http\Controllers\Admin\Keuangan\Saldo;

use App\Http\Controllers\Controller;
use App\Models\master_data\mst_kelas;
use App\Models\master_data\mst_post;
use App\Models\master_data\mst_siswa;
use App\Models\master_data\mst_thn_aka;
use App\Models\scctbill;
use App\Models\scctcust;
use App\Models\sccttran;
use App\Models\SmTopup;
use App\Models\ValidationMessage;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Mockery\Exception;

class SaldoVirtualAccountController extends Controller
{
    public function __construct()
    {
        $this->title = 'Keuangan';
        $this->mainTitla = 'Saldo';
        $this->dataTitle = 'Saldo Virtual Account';
        $this->showTitle = 'Detail Saldo  Virtual Account';


        $this->datasUrl = route('admin.keuangan.saldo.saldo-virtual-account.get-data');
        $this->detailDatasUrl = '';
        $this->columnsUrl = route('admin.keuangan.saldo.saldo-virtual-account.get-column');
    }

    public function index()
    {
        $data['angkatan'] = mst_thn_aka::where('thn_aka', '!=', null)->orderBy('thn_aka','asc')->get();
        $data['kelas'] = mst_kelas::orderBy('kelas','asc')->get();
        $data['post'] = mst_post::orderBy('nama_post','asc')->get();
        $data['title'] = $this->title;
        $data['mainTitle'] = $this->mainTitla;
        $data['dataTitle'] = $this->dataTitle;
        //        $data['showTitle'] = $this->showTitle;
        $data['columnsUrl'] = $this->columnsUrl;
        $data['datasUrl'] = $this->datasUrl;
        $data['modalLink'] = view('admin.keuangan.saldo.saldo_virtual_account.modal', $data)->render();

        return view('admin.keuangan.saldo.saldo_virtual_account.index', $data);
    }

    public function show($id)
    {
        try {
            $decryptedId = Crypt::decrypt($id);

            $data['title'] = $this->title;
            $data['mainTitle'] = $this->mainTitla;
            $data['dataTitle'] = $this->dataTitle;
            $data['showTitle'] = $this->showTitle;
            $data['indexUrl'] = route('admin.keuangan.saldo.saldo-virtual-account.index');
            $data['columnsUrl'] = route('admin.keuangan.saldo.saldo-virtual-account.transaksi.get-column');
            $data['datasUrl'] = route('admin.keuangan.saldo.saldo-virtual-account.transaksi.get-data', ['CUSTID' => $decryptedId]);

            $data['siswa'] = mst_siswa::leftJoin('mst_kelas', 'mst_kelas.id', '=', 'mst_siswas.id_kelas')
                ->leftJoin('mst_thn_aka', 'mst_thn_aka.id', '=', 'mst_siswas.id_thn_aka')
                ->leftJoin('sccttran', 'mst_siswas.id', 'sccttran.CUSTID')
                ->select([
                    'mst_siswas.id',
                    'mst_siswas.nis',
                    'mst_siswas.nisn',
                    'mst_siswas.nama',
                    'mst_kelas.kelas as kelas',
                    'mst_kelas.kelompok as kelompok',
                    'mst_thn_aka.thn_aka as thn_aka',
                ])->selectRaw(
                    'getKredit(mst_siswas.id) - getDebet(mst_siswas.id) as saldo'
                )
                ->find($decryptedId);

            if ($data['siswa']) {
                $data['siswa']->nova = mst_siswa::showVA($data['siswa']->nis);
            }

            //        dd($data['siswa']);
            return view('admin.keuangan.saldo.saldo_virtual_account.show', $data);
        } catch (DecryptException $e) {
            return redirect()->route('admin.keuangan.saldo.saldo-virtual-account.index')->with('error', 'Siswa tidak ditemukan!');
        }
    }

    public function getColumn(Request $request)
    {
        return [
            ['data' => 'no', 'name' => 'no'],
            ['data' => 'nis', 'name' => 'NIS', 'searchable' => true, 'orderable' => true],
            ['data' => 'nama', 'name' => 'NAMA', 'searchable' => true, 'orderable' => true],
            ['data' => 'nova', 'name' => 'NOVA', 'searchable' => true, 'orderable' => true],
            ['data' => 'saldo', 'name' => 'Saldo', 'orderable' => true, 'columnType' => 'currency', 'className' => 'text-end'],
            [
                'data' => 'print',
                'name' => '',
                'columnType' => 'button',
                'className' => 'text-center',
                'button' => 'link',
                'buttonLink' => route('admin.keuangan.saldo.saldo-virtual-account.show', ':id'),
                'buttonText' => 'Detail Transaksi',
                'noCaption' => true,
                'buttonClass' => 'btn btn-sm btn-primary btn-icon btn-print-tagihan',
                'buttonIcon' => 'ri-profile-line'
            ],
        ];
    }

    public function getData(Request $request)
    {
        $filters = [];
        $filterQuery = null;

        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length");

        $columnName_arr = $request->get('columns');
        $search_arr = $request->get('search');

        $defaultColumn = 'sccttran.created_at';
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

        $filter = $request->input('filter');
        if ($filter) {
            foreach ($filter as $key => $val) {
                if (strtolower($val) != 'all' && $val !== null && $val !== '') {
                    $colName = match ($key) {
                        'status' => 'scctbill.PAIDST',
                        'jenis' => 'scctbill.cicil',
                        'kelas' => 'mst_siswas.id_kelas',
                        'tahun_akademik' => 'mst_siswas.id_thn_aka',
                        default => null
                    };
                    ($colName) && $filters[] = [$colName, '=', $val];
                }
            };
            if (!empty($filters)) {
                $filterQuery = function ($query) use ($filters) {
                    foreach ($filters as $filter) {
                        if (count($filter) === 3) {
                            $query->where($filter[0], $filter[1], $filter[2]);
                        } elseif (count($filter) === 4) {
                            $query->{$filter[3]}($filter[0], $filter[1], $filter[2]);
                        }
                    }
                };
            }
        }

        $whereAny = [
            'mst_siswas.nama',
            'mst_siswas.nis',
        ];

        $select = array_merge([
            'mst_siswas.id as id',
            'mst_siswas.nama',
            'mst_siswas.nis',
        ], $whereAny);

        $query = mst_siswa::leftJoin('sccttran', 'mst_siswas.id', 'sccttran.CUSTID')
            ->leftJoin('mst_kelas', 'mst_kelas.id', '=', 'mst_siswas.id_kelas')
            ->leftJoin('mst_thn_aka', 'mst_thn_aka.id', '=', 'mst_siswas.id_thn_aka')
            ->whereAny($whereAny, 'like', '%' . $searchValue . '%')
            ->groupBy('mst_siswas.id')
            ->where(function ($query) use ($filterQuery) {
                if ($filterQuery) {
                    $filterQuery($query);
                }
            });

        // Total records
        $totalRecords = mst_siswa::select('count(mst_siswas.*) as allcount')->count();
        $totalRecordswithFilter = mst_siswa::select('count(mst_siswas.*) as allcount')
            ->whereAny($whereAny, 'like', '%' . $searchValue . '%')
            ->where(function ($query) use ($filterQuery) {
                if ($filterQuery) {
                    $filterQuery($query);
                }
            })
            ->count();

        $records = $query->orderBy('mst_kelas.kelas', 'asc')
            ->orderBy('mst_siswas.nama', 'asc')
            ->orderBy($columnName, $columnSortOrder)
            ->select($select)
            ->selectRaw('getKredit(mst_siswas.id) - getDebet(mst_siswas.id) as saldo')
            ->skip($start)
            ->take($rowperpage)
            ->get()
            ->map(function ($item, $index) {
                $item->no = $index + 1;
                $item->item_id = Crypt::encrypt($item->id);
                $item->print = true;
                $item->nova = mst_siswa::showVA($item->nis);
                unset($item->id);
                return $item;
            })->toArray();

        $response = array(
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecordswithFilter,
            "data" => $records,
        );
        return response()->json($response);
    }

    public function getColumnTran()
    {
        return [
            ['data' => null, 'columnType' => 'row', 'name' => 'No'],
            ['data' => 'METODE', 'name' => 'Metode', 'orderable' => true],
            ['data' => 'TRXDATE', 'name' => 'Tanggal Transaksi', 'orderable' => true, 'columnType' => 'timestamp'],
            ['data' => 'DEBET', 'name' => 'Debet', 'orderable' => true, "className" => "dt-right", 'columnType' => 'currency'],
            ['data' => 'KREDIT', 'name' => 'Kredit', 'orderable' => true, "className" => "dt-right", 'columnType' => 'currency'],
        ];
    }

    public function getDataTran(Request $request)
    {
        $custid = $request->CUSTID;
        $filters = [];
        $filterQuery = null;

        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length");

        $columnName_arr = $request->get('columns');
        $search_arr = $request->get('search');

        $defaultColumn = 'sccttran.created_at';
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

        $filter = $request->input('filter');
        if ($filter) {
            foreach ($filter as $key => $val) {
                if (strtolower($val) != 'all' && $val !== null && $val !== '') {
                    $colName = match ($key) {
                        'status' => 'scctbill.PAIDST',
                        'jenis' => 'scctbill.cicil',
                        'kelas' => 'mst_siswas.id_kelas',
                        'tahun_akademik' => 'mst_siswas.id_thn_aka',
                        default => null
                    };
                    ($colName) && $filters[] = [$colName, '=', $val];
                }
            };
        }

        ($custid) && $filters[] = ['sccttran.CUSTID', '=', $custid];
        if (!empty($filters)) {
            $filterQuery = function ($query) use ($filters) {
                foreach ($filters as $filter) {
                    if (count($filter) === 3) {
                        $query->where($filter[0], $filter[1], $filter[2]);
                    } elseif (count($filter) === 4) {
                        $query->{$filter[3]}($filter[0], $filter[1], $filter[2]);
                    }
                }
            };
        }

        $whereAny = [
            'sccttran.CUSTID',
            'sccttran.METODE',
            'sccttran.TRXDATE',
            'sccttran.NOREFF',
            'sccttran.FIDBANK',
            'sccttran.KDCHANNEL',
            'sccttran.DEBET',
            'sccttran.KREDIT',
            'sccttran.REFFBANK',
            'sccttran.TRANSNO',
            'sccttran.REVERSAL'
        ];

        $select = array_merge($whereAny, [
            'sccttran.id']);

        $query = sccttran::whereAny($whereAny, 'like', '%' . $searchValue . '%')
            ->where(function ($query) use ($filterQuery) {
                if ($filterQuery) {
                    $filterQuery($query);
                }
            });

//        dd($query);

        // Total records
        $totalRecords = sccttran::select('count(sccttran.*) as allcount')->count();
        $totalRecordswithFilter = $query->count();

        $records = $query->orderBy($columnName, $columnSortOrder)
            ->select($select)
            ->skip($start)
            ->take($rowperpage)
            ->get()
            ->map(function ($item, $index) {
                unset($item->id);
                return $item;
            })->toArray();

        $response = array(
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecordswithFilter,
            "data" => $records,
        );
        return response()->json($response);
    }

    public function getSaldo(Request $request)
    {
        if ($request->siswa) {
//            return scctcust::where('CUSTID', $request->siswa)->firstOrFail();
            $saldo = sccttran::selectRaw(
                'COALESCE(SUM(KREDIT), 0) - COALESCE(SUM(DEBET), 0) as saldo'
                )->where('CUSTID', $request->siswa)
                ->first();

            return $saldo->saldo ?? 0;
        } else {
            return 0;
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'siswa' => ['required'],
                'total_top_up' => ['required'],
            ],
            ValidationMessage::messages(),
            ValidationMessage::attributes()
        );

        if ($validator->fails()) return response()->json(['message' => 'Silahkan periksa form anda', 'error' => $validator->errors()], 422);

        $nominal = str_replace('.', '', $request->total_top_up);
        if (!is_numeric($nominal)) return response()->json(['message' => 'Nominal tidak boleh kosong'], 422);
        $siswa = mst_siswa::select('id', 'nama', 'nis')->where('id', $request->siswa)->first();
        if (!$siswa) return response()->json(['message' => 'Data siswa tidak ditemukan'], 422);

        try {
            DB::beginTransaction();
            $tglTran = now();

            sccttran::create([
                'CUSTID' => $siswa->id,
                'METODE' => 'Manual Top Up',
                'TRXDATE' => $tglTran,
                'KREDIT' => $nominal,
            ]);

            $smtopup = SmTopup::create([
                'CUSTID' => $siswa->id,
                'TRXDATE' => $tglTran,
                'NOMINAL' => $nominal,
            ]);
            $smtopup->TOPUPNO = date('Ymd').'-'.$smtopup->id;
            $smtopup->save();

            DB::commit();
            return response()->json(['message' => 'Top Up sebesar Rp ' . $request->total_top_up . ' untuk siswa: ' . $siswa->nama . ' berhasil!'], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'Top up Gagal', 'error' => $e], 422);
        }
    }

    public function tarik(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'siswa' => ['required'],
                'total' => ['required'],
            ],
            ValidationMessage::messages(),
            ValidationMessage::attributes()
        );

        if ($validator->fails()) return response()->json(['message' => 'Silahkan periksa form anda', 'error' => $validator->errors()], 422);

        $nominal = str_replace('.', '', $request->total);
        if (!is_numeric($nominal)) return response()->json(['message' => 'Nominal tidak boleh kosong'], 422);

        $siswa = mst_siswa::leftJoin('sccttran', 'mst_siswas.id', 'sccttran.CUSTID')
            ->select('mst_siswas.id', 'mst_siswas.nama', 'mst_siswas.nis')
            ->selectRaw('getKredit(mst_siswas.id) - getDebet(mst_siswas.id) as saldo')
            ->find($request->siswa);

        if (!$siswa) return response()->json(['message' => 'Data siswa tidak ditemukan'], 422);
        if ($siswa->saldo < $nominal) return response()->json(['message' => 'Saldo siswa kurang<br> Saldo siswa: ' . $siswa->saldo], 422);
        try {
            DB::beginTransaction();
            sccttran::create([
                'CUSTID' => $siswa->id,
                'METODE' => 'From Saldo To Cash',
                'TRXDATE' => now(),
                'DEBET' => $nominal,
            ]);
            DB::commit();
            return response()->json(['message' => 'Penarikan sebesar Rp ' . $request->total . ' untuk siswa: ' . $siswa->nama . 'berhasil!'], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'Penarikan Gagal', 'error' => $e], 422);
        }
    }
}
