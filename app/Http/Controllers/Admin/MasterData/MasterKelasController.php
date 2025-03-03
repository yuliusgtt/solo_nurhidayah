<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MasterData\mst_kelas;
use App\Models\MasterData\mst_sekolah;
use App\Models\ValidationMessage;

use Exception;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MasterKelasController extends Controller
{
    public string $title;
    public string $mainTitle;
    public string $dataTitle;
    public string $showTitle;

    public function __construct()
    {
        $this->title = 'Master Data';
        $this->mainTitle = 'Master Kelas';
        $this->dataTitle = 'Master Kelas';
    }

    public function index()
    {
        $data['unit'] = mst_sekolah::select('DESC01')->get();
        $data['title'] = $this->title;
        $data['mainTitle'] = $this->mainTitle;
        $data['dataTitle'] = $this->dataTitle;
//        $data['modalLink'] = view('admin.master_data.data_siswa.modal', compact('kelas', 'angkatan'));
        $data['columnsUrl'] = route('admin.master-data.master-kelas.get-column');
        $data['datasUrl'] = route('admin.master-data.master-kelas.get-data');

        return view('admin.master_data.master_kelas.index', $data);
    }

    public function getColumn()
    {
        return [
            ['data' => null, 'name' => 'no', 'className' => 'text-center', 'columnType' => 'row'],
            ['data' => 'unit', 'name' => 'Unit', 'searchable' => true, 'orderable' => true],
            ['data' => 'jenjang', 'name' => 'KELAS', 'searchable' => true, 'orderable' => true],
            ['data' => 'kelas', 'name' => 'KELOMPOK', 'searchable' => true, 'orderable' => true],
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

        $columnName = 'unit';
        $columnSortOrder = 'asc';

        if (!empty($order_arr)) {
            $columnIndex = $columnIndex_arr[0]['column'] ?? null;
            if ($columnIndex !== null && !empty($columnName_arr[$columnIndex]['data']) && $columnName_arr[$columnIndex]['data'] !== 'no') {
                $columnName = $columnName_arr[$columnIndex]['data'];
                $columnSortOrder = $order_arr[0]['dir'] ?? 'desc';
            }
        }

        // Total records
        $totalRecords = mst_kelas::select('count(*) as allcount')->count();
        $totalRecordswithFilter = mst_kelas::select('count(*) as allcount')
            ->whereAny(['kelas', 'jenjang', 'unit'], 'like', '%' . $searchValue . '%')
            ->count();

        // Fetch records
        $records = mst_kelas::orderBy($columnName, $columnSortOrder)
            ->orderBy('jenjang', 'asc')
            ->orderBy('kelas', 'asc')
            ->whereAny(['kelas', 'jenjang', 'unit'], 'like', '%' . $searchValue . '%')
            ->select('*')
            ->skip($start)
            ->take($rowperpage)
            ->get()
            ->map(function ($item, $index) {
                $item->item_id = Crypt::encrypt($item->id);
//                $item->edit = true;
//                $item->delete = true;
                unset($item->id);
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

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'unit' => ['required',],
                'kelas' => ['required',],
                'kelompok' => ['required'],
            ],
            ValidationMessage::messages(),
            ValidationMessage::attributes()
        );

        if ($validator->fails()) return response()->json(['message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);

        $kelasExist = mst_kelas::where('jenjang', $request->kelas)
            ->where('kelas', $request->kelompok)
            ->where('unit', $request->unit)
            ->first();
        if ($kelasExist) return response()->json(['message' => 'Kelas sudah ada'], 422);

        try {
            DB::beginTransaction();
            mst_kelas::create(
                [
                    'jenjang' => $request->kelas,
                    'kelas' => $request->kelompok,
                    'unit' => $request->unit,
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
