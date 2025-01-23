<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MasterData\mst_kelas;
use App\Models\MasterData\mst_thn_aka;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class TahunPelajaranController extends Controller
{
    public string $title = 'Master Data';
    public string $mainTitle = 'Tahun Pelajaran';
    public string $dataTitle = 'Tahun Pelajaran';

    public function index()
    {
        //        dd($angkatan);
        $data['title'] = $this->title;
        $data['mainTitle'] = $this->mainTitle;
        $data['dataTitle'] = $this->dataTitle;
//        $data['modalLink'] = view('admin.master_data.data_siswa.modal', compact('kelas', 'angkatan'));
        $data['columnsUrl'] = route('admin.master-data.tahun-pelajaran.get-column');
        $data['datasUrl'] = route('admin.master-data.tahun-pelajaran.get-data');

        return view('admin.master_data.master_kelas.index', $data);
    }

    public function getColumn()
    {
        return [
            ['data' => 'no', 'name' => 'no', 'className' => 'text-center'],
            ['data' => 'thn_aka', 'name' => 'Tahun Pelajaran', 'searchable' => true, 'orderable' => true],
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

        $defaultColumn = 'thn_aka';
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

        // Total records
        $totalRecords = mst_thn_aka::select('count(*) as allcount')->count();
        $totalRecordswithFilter = mst_thn_aka::select('count(*) as allcount')
            ->whereAny(['thn_aka'], 'like', '%' . $searchValue . '%')
            ->count();

        // Fetch records
        $records = mst_thn_aka::orderBy($columnName, $columnSortOrder)
            ->whereAny(['thn_aka'], 'like', '%' . $searchValue . '%')
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
