<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MasterData\mst_kelas;
use App\Models\MasterData\mst_thn_aka;
use App\Models\MasterData\u_akun;
use App\Models\ValidationMessage;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MasterPostController extends Controller
{
    public string $title = 'Master Data';
    public string $mainTitle = 'Master Post';
    public string $dataTitle = 'Master Post';

    public function index()
    {
        //        dd($angkatan);
        $data['title'] = $this->title;
        $data['mainTitle'] = $this->mainTitle;
        $data['dataTitle'] = $this->dataTitle;
//        $data['modalLink'] = view('admin.master_data.data_siswa.modal', compact('kelas', 'angkatan'));
        $data['columnsUrl'] = route('admin.master-data.master-post.get-column');
        $data['datasUrl'] = route('admin.master-data.master-post.get-data');

        return view('admin.master_data.master_post.index', $data);
    }

    public function getColumn()
    {
        return [
            ['data' => null, 'name' => 'no', 'className' => 'text-center','columnType' => 'row'],
            ['data' => 'KodeAkun', 'name' => 'Kode', 'searchable' => true, 'orderable' => true],
            ['data' => 'NamaAkun', 'name' => 'Nama Post', 'searchable' => true, 'orderable' => true],
            ['data' => 'NoRek', 'name' => 'Nomor Rekening', 'searchable' => true, 'orderable' => true],
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

        $columnName = 'KodeAkun';
        $columnSortOrder = 'asc';

        if (!empty($order_arr)) {
            $columnIndex = $columnIndex_arr[0]['column'] ?? null;
            if ($columnIndex !== null && !empty($columnName_arr[$columnIndex]['data']) && $columnName_arr[$columnIndex]['data'] !== 'no') {
                $columnName = $columnName_arr[$columnIndex]['data'];
                $columnSortOrder = $order_arr[0]['dir'] ?? 'desc';
            }
        }

        // Total records
        $totalRecords = u_akun::select('count(*) as allcount')->count();
        $totalRecordswithFilter = u_akun::select('count(*) as allcount')
            ->whereAny(['KodeAkun','NamaAkun','NoRek'], 'like', '%' . $searchValue . '%')
            ->count();

        // Fetch records
        $records = u_akun::orderBy($columnName, $columnSortOrder)
            ->whereAny(['KodeAkun','NamaAkun','NoRek'], 'like', '%' . $searchValue . '%')
            ->select('*')
            ->skip($start)
            ->take($rowperpage)
            ->get()
            ->toArray();

        $response = array(
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecordswithFilter,
            'data' => $records,
        );
        return response()->json($response);
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'kode_akun' => ['required',],
                'nama_akun' => ['required',],
                'no_rek' => [],
            ],
            ValidationMessage::messages(),
            ValidationMessage::attributes()
        );

        if ($validator->fails()) return response()->json(['message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);

        $kelasExist = u_akun::where([
                ['KodeAkun', '=', $request->kode_akun],
                ['NamaAkun', '=', $request->nama_akun],
                ['NoRek', '=', $request->no_rek],
            ])
            ->first();
        if ($kelasExist) return response()->json(['message' => 'Kelas sudah ada'], 422);

        try {
            DB::beginTransaction();
            u_akun::create(
                [
                    'KodeAkun' => $request->kode_akun,
                    'NamaAkun' => $request->nama_akun,
                    'unit' => $request->no_rek,
                ]
            );
            DB::commit();
            return response()->json(['message' => 'Data ' . $this->mainTitle . ' telah disimpan']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Data ' . $this->mainTitle . ' gagal disimpan', 'error' => $e->getMessage()], 422);
        }
    }
}
