<?php

namespace App\Http\Controllers\Admin\Keuangan\Saldo;

use App\Http\Controllers\Controller;
use App\Models\MasterData\mst_kelas;
use App\Models\MasterData\mst_thn_aka;
use App\Models\scctbill;
use App\Models\scctcust;
use App\Models\sccttran;
use App\Models\ValidationMessage;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
        $data['thn_aka'] = mst_thn_aka::select(['thn_aka'])->where('thn_aka', '!=', null)->get();
        $data['kelas'] = mst_kelas::get();
        $data['title'] = $this->title;
        $data['mainTitle'] = $this->mainTitla;
        $data['dataTitle'] = $this->dataTitle;
        //        $data['showTitle'] = $this->showTitle;
        $data['columnsUrl'] = $this->columnsUrl;
        $data['datasUrl'] = $this->datasUrl;

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

            $data['siswa'] = scctcust::find($decryptedId);

            if ($data['siswa']) {
                if ($data['siswa']->NOCUST && $data['siswa']->NOCUST != '-') {
                    $NOVA = scctcust::showVA($data['siswa']->NOCUST);
                } else {
                    $NOVA = scctcust::showVA($data['siswa']->NUM2ND);
                }
                $data['siswa']->NOVA = $NOVA;
//                $data['siswa']-> = $NOVA;
            }

            return view('admin.keuangan.saldo.saldo_virtual_account.show', $data);
        } catch (DecryptException $e) {
            return redirect()->route('admin.keuangan.saldo.saldo-virtual-account.index')->with('error', 'Siswa tidak ditemukan!');
        }
    }

    public function getColumn(Request $request)
    {
        return [
            ['data' => null, 'name' => 'no', 'columnType' => 'row'],
            ['data' => 'NOCUST', 'name' => 'NIS', 'searchable' => true, 'orderable' => true],
            ['data' => 'NOVA', 'name' => 'NO VA'],
            ['data' => 'NMCUST', 'name' => 'NAMA', 'searchable' => true, 'orderable' => true],
            ['data' => 'NUM2ND', 'name' => 'No Pendaftaran', 'searchable' => true, 'orderable' => true],
            ['data' => 'CODE02', 'name' => 'Unit', 'searchable' => true, 'orderable' => true],
            ['data' => 'DESC02', 'name' => 'Kelas', 'searchable' => true, 'orderable' => true],
            ['data' => 'DESC03', 'name' => 'Jenjang', 'searchable' => true, 'orderable' => true],
            ['data' => 'DESC04', 'name' => 'Angkatan', 'searchable' => true, 'orderable' => true],
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

        $defaultColumn = 'scctcust.NOCUST';
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
//                        'kelas' => 'scctcust.DESC02',
                        'sekolah' => 'scctcust.CODE02',
                        'siswa' => 'scctcust.nmcust',
                        'thn_aka' => 'scctcust.DESC04',
                        default => null
                    };
                    if ($key == 'siswa') {
                        $val = is_numeric($val) ? $val : '%' . $val . '%';
                        $colName = is_numeric($val) ? 'scctcust.NOCUST' : $colName;
                        ($colName) && $filters[] = [$colName, 'like', $val];
                    } else if ($key == 'kelas') {
                        $val = explode(",", $val);
                        if (count($val) == 3) {
                            $filters[] = ['scctcust.CODE02', '=', $val[0]];
                            $filters[] = ['scctcust.DESC02', '=', $val[1]];
                            $filters[] = ['scctcust.DESC03', '=', $val[2]];
                        }
                    } else {
                        ($colName) && $filters[] = [$colName, '=', $val];
                    }
                }
            }

            if (!empty($filters)) {
                $filterQuery = function ($query) use ($filters) {
                    foreach ($filters as $filter) {
                        if (count($filter) === 3) {
                            $query->where($filter[0], $filter[1], $filter[2]);
                        } elseif (count($filter) === 4) {
                            if ($filter[3] == 'whereBetween') {
                                $query->whereBetween($filter[0], [$filter[1], $filter[2]]);
                            } else {
                                $query->{$filter[3]}($filter[0], $filter[1], $filter[2]);
                            }
                        }
                    }
                };
            }
        }

        $whereAny = [
            'scctcust.NMCUST',
            'scctcust.NOCUST',
            'scctcust.NUM2ND',
        ];

        $select = array_unique(array_merge($whereAny, [
            'scctcust.CODE02',
            'scctcust.DESC02',
            'scctcust.DESC03',
            'scctcust.CUSTID',
            'scctcust.DESC04',
        ]));

        $totalRecords = Cache::remember('scctcust_total_count', 600, function () {
            return scctcust::select('count(*) as allcount')->count();
        });

        $query = scctcust::whereAny($whereAny, 'like', '%' . $searchValue . '%');
        if ($filterQuery) {
            $query->where(function ($q) use ($filterQuery) {
                $filterQuery($q);
            });
        }

        $totalRecordswithFilter = $query->select('count(*) as allcount')->count();

        $records = $query->leftJoin('sccttran', 'sccttran.CUSTID', '=', 'scctcust.CUSTID')->select($select)
            ->selectRaw('COALESCE(SUM(sccttran.KREDIT), 0) - COALESCE(SUM(DEBET), 0) as saldo')
            ->orderBy($columnName, $columnSortOrder)
            ->skip($start)
            ->take($rowperpage)
            ->groupBy('scctcust.CUSTID')
            ->get()
            ->map(function ($item) {
                $item->item_id = Crypt::encrypt($item->CUSTID);
                $item->print = true;
                if ($item->NOCUST && $item->NOCUST != '-') {
                    $NOVA = scctcust::showVA($item->NOCUST);
                } else {
                    $NOVA = scctcust::showVA($item->NUM2ND);
                }
                $item->NOVA = $NOVA;
                unset($item->CUSTID);
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

        $defaultColumn = 'sccttran.TRXDATE';
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
            'scctcust.NMCUST',
            'scctcust.NOCUST',
            'scctcust.NUM2ND',
            'sccttran.METODE',

        ];

        $select = array_merge($whereAny, [
            'sccttran.METODE',
            'sccttran.TRXDATE',
            'sccttran.NOREFF',
            'sccttran.FIDBANK',
            'sccttran.KDCHANNEL',
            'sccttran.DEBET',
            'sccttran.KREDIT',
            'sccttran.REFFBANK',
            'sccttran.TRANSNO',
        ]);

        $query = sccttran::whereAny($whereAny, 'like', '%' . $searchValue . '%')
            ->leftJoin('scctcust', 'scctcust.CUSTID', 'sccttran.CUSTID')
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
                ->groupBy('CUSTID')
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
            $smtopup->TOPUPNO = date('Ymd') . '-' . $smtopup->id;
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
