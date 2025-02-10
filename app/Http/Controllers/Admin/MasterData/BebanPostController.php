<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MasterData\mst_kelas;
use App\Models\MasterData\mst_tagihan;
use App\Models\MasterData\mst_thn_aka;
use App\Models\MasterData\u_akun;
use App\Models\MasterData\u_daftar_harga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BebanPostController extends Controller
{
    public string $title = 'Master Data';
    public string $mainTitle = 'Beban Post';
    public string $dataTitle = 'Beban Post';

    public function index()
    {
        $data['title'] = $this->title;
        $data['mainTitle'] = $this->mainTitle;
        $data['dataTitle'] = $this->dataTitle;
        $data['thn_aka'] = mst_thn_aka::orderBy('thn_aka', 'asc')->get();
        $data['tagihan'] = u_akun::whereNotNull('KodeAkun')->orderBy('NamaAkun', 'asc')->get();
        $data['jenjang'] = mst_kelas::select('jenjang')->distinct()
            ->orderByRaw("CASE WHEN jenjang REGEXP '^[0-9]+$' THEN 0 ELSE 1 END, jenjang")->get();
        $data['kelas'] = mst_kelas::select('kelas')->distinct('kelas')
            ->orderByRaw("CASE WHEN kelas REGEXP '^[0-9]+$' THEN 0 ELSE 1 END, kelas")->get();
//        $data['modalLink'] = view('admin.master_data.data_siswa.modal', compact('kelas', 'angkatan'));
        $data['columnsUrl'] = route('admin.master-data.beban-post.get-column');
        $data['datasUrl'] = route('admin.master-data.beban-post.get-data');

        return view('admin.master_data.beban_post.index', $data);
    }

    public function getColumn()
    {
        return [
            ['data' => 'no', 'name' => 'no', 'className' => 'text-center'],
            ['data' => 'KodeAkun', 'name' => 'Kode', 'searchable' => true, 'orderable' => true],
            ['data' => 'NamaAkun', 'name' => 'Nama Post', 'searchable' => true, 'orderable' => true],
            ['data' => 'nominal', 'name' => 'Nominal','columnType' => 'currency', 'searchable' => true, 'orderable' => true],
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

        $defaultColumn = 'u_daftar_harga.KodeAkun';
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
        $whereAny = [
            'u_daftar_harga.KodeAkun', 'u_akun.NamaAkun', 'u_daftar_harga.nominal'
        ];

        $select = array_unique(array_merge($whereAny, [

        ]));

        // Total records

        $totalRecords = Cache::remember('scctcust_total_count', 600, function () {
            return u_daftar_harga::select('count(*) as allcount')->count();
        });

        $query =  u_daftar_harga::leftJoin('u_akun', 'u_akun.KodeAkun', '=', 'u_daftar_harga.KodeAkun')
            ->whereAny($select, 'like', '%' . $searchValue . '%');

        $totalRecordswithFilter = $query->select('count(*) as allcount')->count();

        // Fetch records
        $records = $query->orderBy($columnName, $columnSortOrder)
            ->select('*')
            ->skip($start)
            ->take($rowperpage)
            ->get()
            ->map(function ($item, $index) {
                $item->no = $index + 1;
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
}
