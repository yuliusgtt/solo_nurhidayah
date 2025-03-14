<?php

namespace App\Http\Controllers\Admin\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\MasterData\mst_kelas;
use App\Models\MasterData\mst_tagihan;
use App\Models\MasterData\mst_thn_aka;
use App\Models\scctbill;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class HapusTagihanController extends Controller
{
    public function __construct()
    {
        $this->title = 'Hapus Tagihan Siswa';
        $this->datasUrl = route('admin.keuangan.hapus-tagihan.get-data');
        $this->columnsUrl = route('admin.keuangan.hapus-tagihan.get-column');
    }

    public function index()
    {
        $data['title'] = $this->title;
        $data['columnsUrl'] = $this->columnsUrl;
        $data['datasUrl'] = $this->datasUrl;
        $data['post'] = mst_tagihan::select(['tagihan'])->get();
        $data['thn_aka'] = mst_thn_aka::select(['thn_aka'])->where('thn_aka', '!=', null)->get();
        $data['kelas'] = mst_kelas::get();

        return view('admin.keuangan.tagihan_siswa.data_tagihan', $data);
    }

    public function getColumn()
    {
        return [
            ['data' => null, 'name' => 'no', 'columnType' => 'row'],
            ['data' => 'nocust', 'name' => 'NIS', 'searchable' => true, 'orderable' => true],
            ['data' => 'nmcust', 'name' => 'NAMA', 'searchable' => true, 'orderable' => true],
            ['data' => 'CODE02', 'name' => 'Unit', 'searchable' => true, 'orderable' => true],
            ['data' => 'DESC02', 'name' => 'Kelas', 'searchable' => true, 'orderable' => true],
            ['data' => 'BILLNM', 'name' => 'Nama Tagihan', 'searchable' => true, 'orderable' => true],
            ['data' => 'BILLAM', 'name' => 'Tagihan', 'searchable' => true, 'orderable' => true, 'columnType' => 'currency', 'className' => 'text-end'],
            ['data' => 'BTA', 'name' => 'Tahun AKA', 'searchable' => true, 'orderable' => true],
            [
                'data' => 'delete',
                'name' => '',
                'columnType' => 'button',
                'className' => 'text-center',
                'button' => 'modal',
                'buttonText' => 'Hapus',
                'buttonClass' => 'btn btn-sm btn-danger',
                'buttonLink' => '#modal-delete',
                'buttonIcon' => 'ri-delete-bin-line'
            ],
        ];
    }

    public function getData(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length");
        $columnName_arr = $request->get('columns');
        $search_arr = $request->get('search');

        $defaultColumn = 'scctbill.PAIDDT';
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

        $filters = [];
        $filterQuery = null;

        $filter = $request->input('filter');
        if ($filter) {
            foreach ($filter as $key => $val) {
                if (strtolower($val) != 'all' && $val !== null && $val !== '') {
                    $colName = match ($key) {
                        'tanggal-pembuatan' => 'scctbill.FTGLTagihan',
                        'tahun_akademik' => 'scctbill.BTA',
                        'post' => 'scctbill.BILLNM',
                        'kelas' => 'scctcust.DESC02',
                        'siswa' => 'scctcust.nmcust',
                        default => null
                    };
                    if ($key == 'tanggal-pembuatan') {
                        if (preg_match('/^\d{2}-\d{2}-\d{4} [-\/~] \d{2}-\d{2}-\d{4}$/', $val)) {
                            $val = preg_replace('/[-\/~]/', '-', $val);

                            list($startDate, $endDate) = explode(' - ', $val);
                            $startDate = Carbon::createFromFormat('d-m-Y', $startDate)->startOfDay();
                            $endDate = Carbon::createFromFormat('d-m-Y', $endDate)->endOfDay();
                            if ($startDate && $endDate) {
                                ($colName) && $filters[] = [$colName, $startDate, $endDate, 'whereBetween'];
                            }
                        }
                    } elseif ($key == 'siswa') {
                        $val = is_numeric($val) ? $val : '%' . $val . '%';
                        $colName = is_numeric($val) ? 'scctcust.nocust' : $colName;
                        ($colName) && $filters[] = [$colName, 'like', $val];
                    } else {
                        ($colName) && $filters[] = [$colName, '=', $val];
                    }
                }
            };

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
            ->where('scctbill.PAIDST', 0)
            ->where('scctbill.FSTSBolehBayar', 1)
            ->whereAny($whereAny, 'like', '%' . $searchValue . '%')
            ->where(function ($query) use ($filterQuery) {
                if ($filterQuery) {
                    $filterQuery($query);
                }
            });

        $totalRecords = Cache::remember('total_tagihan_count', 600, function () {
            return  scctbill::select('count(*) as allcount')
                ->where('scctbill.FSTSBolehBayar', 1)
                ->where('scctbill.PAIDST', 0)
                ->count();
        });

        $totalRecordswithFilter = (clone $query)->count();

        $records = (clone $query)->orderBy($columnName, $columnSortOrder)
            ->select($select)
            ->whereAny($whereAny, 'like', '%' . $searchValue . '%')
            ->where(function ($query) use ($filterQuery) {
                if ($filterQuery) {
                    $filterQuery($query);
                }
            })
            ->skip($start)
            ->take($rowperpage)
            ->get()
            ->map(function ($item, $index) {
                $item->delete = true;
                return $item;
            })->toArray();
        $response = array(
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords ?? 0,
            "recordsFiltered" => $totalRecordswithFilter ?? 0,
            "data" => $records ?? [],
        );
        return response()->json($response);
    }

    public function destroy($id, Request $request)
    {
        dd($request);
    }
}
