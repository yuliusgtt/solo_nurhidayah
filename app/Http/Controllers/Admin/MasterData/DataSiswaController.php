<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\master_data\mst_siswa;
use App\Models\MasterData\mst_kelas;
use App\Models\MasterData\mst_sekolah;
use App\Models\MasterData\mst_thn_aka;
use App\Models\MasterData\u_akun;
use App\Models\scctcust;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class DataSiswaController extends Controller
{
    public string $title = 'Master Data';
    public string $mainTitle = 'Data Siswa';
    public string $dataTitle = 'Data Siswa';

    public function index()
    {
        $data['title'] = $this->title;
        $data['mainTitle'] = $this->mainTitle;
        $data['dataTitle'] = $this->dataTitle;
//        $data['modalLink'] = view('admin.master_data.data_siswa.modal', compact('kelas', 'angkatan'));
        $data['columnsUrl'] = route('admin.master-data.data-siswa.get-column');
        $data['datasUrl'] = route('admin.master-data.data-siswa.get-data');
        $data['thn_aka'] = mst_thn_aka::orderBy('thn_aka', 'desc')->get();
//        dd($data['thn_aka']);
        $data['sekolah'] = mst_sekolah::get();
        $data['kelas'] = mst_kelas::orderByRaw("CASE WHEN kelas REGEXP '^[0-9]+$' THEN 0 ELSE 1 END, kelas")->get();

        return view('admin.master_data.data_siswa.index', $data);
    }

    public function getColumn()
    {
        return [
            ['data' => null, 'name' => 'no', 'className' => 'text-center', 'columnType' => 'row'],
            ['data' => 'NOCUST', 'name' => 'NIS', 'searchable' => true, 'orderable' => true],
            ['data' => 'NOVA', 'name' => 'NO VA'],
            ['data' => 'NMCUST', 'name' => 'NAMA', 'searchable' => true, 'orderable' => true],
            ['data' => 'NUM2ND', 'name' => 'No Pendaftaran', 'searchable' => true, 'orderable' => true],
            ['data' => 'CODE02', 'name' => 'Unit', 'searchable' => true, 'orderable' => true],
            ['data' => 'DESC02', 'name' => 'Kelas', 'searchable' => true, 'orderable' => true],
            ['data' => 'DESC03', 'name' => 'Jenjang', 'searchable' => true, 'orderable' => true],
            ['data' => 'DESC04', 'name' => 'Angkatan', 'searchable' => true, 'orderable' => true],
            [
                'data' => 'edit',
                'name' => 'Edit',
                'columnType' => 'button',
                'className' => 'text-center',
                'button' => 'modal',
                'buttonText' => 'Edit',
                'buttonClass' => 'btn btn-sm btn-warning',
                'buttonLink' => '#modal-edit',
                'buttonIcon' => 'tf-icon ri-pencil-line me-2'
            ],
            [
                'data' => 'delete',
                'name' => 'Hapus',
                'columnType' => 'button',
                'className' => 'text-center',
                'button' => 'modal',
                'buttonText' => 'Hapus',
                'buttonClass' => 'btn btn-sm btn-danger',
                'buttonLink' => '#modal-delete',
                'buttonIcon' => 'tf-icon ri-delete-bin-5-line me-2'
            ],
        ];
    }

    public function getData(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get('start');
        $rowperpage = $request->get('length');

        $columnName_arr = $request->get('columns');
        $search_arr = $request->get('search');

        $defaultColumn = 'scctcust.nocust';
        $defaultOrder = 'asc';

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
                        if (count($val) == 3){
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

//        dd($filters);
//
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

        $totalRecords = Cache::remember('scctcust_total_count', 600, function () {
            return scctcust::select('count(*) as allcount')->count();
        });

        $query = scctcust::whereAny($whereAny, 'like', '%' . $searchValue . '%');
        if ($filterQuery) {
            $query->where(function ($q) use ($filterQuery) {
                $filterQuery($q);
            });
        }

        $totalRecordswithFilter = $query->count();

        // Fetch records
        $records = $query->orderBy($columnName, $columnSortOrder)
            ->select($select)
            ->skip($start)
            ->take($rowperpage)
            ->get()
            ->map(function ($item) {
                if ($item->NOCUST && $item->NOCUST != '-') {
                    $NOVA = scctcust::showVA($item->NOCUST);
                } else {
                    $NOVA = scctcust::showVA($item->NUM2ND);
                }
                $item->NOVA = $NOVA;
                if (!$item->NOCUST) $item->NOCUST = '-';
                $item->edit = true;
                $item->delete = true;
                return $item;
            })->toArray();

        $response = array(
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecordswithFilter,
            'data' => $records,
        );
        return response()->json($response);
    }

    public function getSiswaSelect2(Request $request)
    {
        $nis = $nodaftar = $nama = null;

        if (!empty($request->term)) {
            if (is_numeric($request->term)) {
                $nis = '%' . $request->term . '%';
                $nodaftar = '%' . $request->term . '%';
            } else {
                $nama = '%' . $request->term . '%';
            }
        }

        if ($request->nis) {
            $nodaftar = null;
        } else if ($request->nodaftar) {
            $nis = null;
        }


        $whereAny = [
            'scctcust.NMCUST',
            'scctcust.NOCUST',
        ];

        $select = array_unique(array_merge($whereAny, [
            'scctcust.CUSTID',
            'scctcust.NUM2ND',
            'scctcust.CODE02',
            'scctcust.DESC02',
            'scctcust.DESC03',
            'scctcust.DESC04',
        ]));

        if (!$nis && !$nama && !$nodaftar) {
            $siswa = [];
        } else {
            $siswa = scctcust::when($nis, function ($query, $nis) {
                return $query->orWhere('scctcust.NOCUST', 'like', $nis);
            })->when($nodaftar, function ($query, $nodaftar) {
                return $query->orWhere('scctcust.NUM2ND', 'like', $nodaftar);
            })->when($nama, function ($query, $nama) {
                return $query->orWhere('scctcust.NMCUST', 'like', $nama);
            })->select($select)
                ->orderBy('scctcust.NMCUST', 'asc')
                ->get()
                ->map(function ($item) {
                    $item->id = $item->CUSTID;
                    $item->text = $item->NOCUST . ' - ' . $item->NMCUST . ' | ' . $item->CODE02 . ' - ' . $item->DESC02 . ' - ' . $item->DESC03 . ' - ' . $item->DESC04;
                    return $item;
                })
                ->toArray();
        }

        return response()->json($siswa);
    }

}
