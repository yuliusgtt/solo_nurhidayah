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
            ['data' =>  null, 'name' => 'no', 'className' => 'text-center', 'columnType' => 'row'],
            ['data' => 'KodeAkun', 'name' => 'Kode', 'searchable' => true, 'orderable' => true],
            ['data' => 'NamaAkun', 'name' => 'Nama Post', 'searchable' => true, 'orderable' => true],
            ['data' => 'nominal', 'name' => 'Nominal', 'columnType' => 'currency', 'searchable' => true, 'orderable' => true],
        ];
    }

    public function getData(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length");

        $columnIndex_arr = $request->get('order', []);
        $columnName_arr = $request->get('columns', []);
        $order_arr = $request->get('order', []);
        $search_arr = $request->get('search', []);
        $searchValue = $search_arr['value'] ?? '';

        $columnName = 'u_daftar_harga.KodeAkun';
        $columnSortOrder = 'asc';

        if (!empty($order_arr)) {
            $columnIndex = $columnIndex_arr[0]['column'] ?? null;
            if ($columnIndex !== null && !empty($columnName_arr[$columnIndex]['data']) && $columnName_arr[$columnIndex]['data'] !== 'no') {
                $columnName = $columnName_arr[$columnIndex]['data'];
                $columnSortOrder = $order_arr[0]['dir'] ?? 'desc';
            }
        }

        $whereAny = [
            'u_daftar_harga.KodeAkun', 'u_akun.NamaAkun', 'u_daftar_harga.nominal'
        ];

        $select = array_unique(array_merge($whereAny, [

        ]));

        // Total records

        $totalRecords = Cache::remember('master_data_beban_post_total_count', 600, function () {
            return u_daftar_harga::select('count(*) as allcount')->count();
        });

        $query = u_daftar_harga::leftJoin('u_akun', 'u_akun.KodeAkun', '=', 'u_daftar_harga.KodeAkun')
            ->whereAny($select, 'like', '%' . $searchValue . '%');

        $totalRecordswithFilter = $query->select('count(*) as allcount')->count();

        // Fetch records
        $records = $query->orderBy($columnName, $columnSortOrder)
            ->select($select)
            ->skip($start)
            ->take($rowperpage)
            ->get()
            ->toArray();

        if ($totalRecords < $totalRecordswithFilter) {
            $totalRecords = $totalRecordswithFilter;
            Cache::put('master_data_beban_post_total_count', $totalRecordswithFilter, 600);
        }

        $response = array(
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecordswithFilter,
            'data' => $records,
        );
        return response()->json($response);
    }
}
