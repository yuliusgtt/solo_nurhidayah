<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MasterData\mst_kelas;
use App\Models\scctcust;
use App\Models\MasterData\mst_thn_aka;
use App\Models\ValidationMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PindahKelasController extends Controller
{
    public function __construct()
    {
        $this->title = 'Utilitas';
        $this->mainTitle = 'Edit Kelas';
        $this->dataTitle = 'Edit Kelas';
        $this->showTitle = 'Detail Kelas';
        $this->datasUrl = route('admin.master-data.pindah-kelas.get-data');
        $this->detailDatasUrl = '';
        $this->columnsUrl = route('admin.master-data.pindah-kelas.get-column');
    }

    public function index()
    {
        $data['title'] = $this->title;
        $data['mainTitle'] = $this->mainTitle;
        $data['dataTitle'] = $this->dataTitle;
        $data['showTitle'] = $this->showTitle;
        $data['columnsUrl'] = $this->columnsUrl;
        $data['datasUrl'] = $this->datasUrl;
        $data['thn_aka'] = mst_thn_aka::where('thn_aka', '!=', null)->orderBy('thn_aka','asc')->get();
        $data['kelas'] = mst_kelas::orderBy('kelas','asc')->get();

        return view('admin.master_data.pindah_kelas.index', $data);
    }

    public function getColumn()
    {
        return [
            //        ['data' => 'no', 'name' => 'no'],
            ['data' => 'check', 'name' => '', 'columnType' => 'checkbox', 'orderable' => false],
            ['data' => 'nis', 'name' => 'NIS', 'searchable' => true, 'orderable' => true],
            ['data' => 'nama', 'name' => 'NAMA', 'searchable' => true, 'orderable' => true],
            ['data' => 'thn_aka', 'name' => 'Angkatan', 'searchable' => true, 'orderable' => true],
            ['data' => 'kelas', 'name' => 'Kelas', 'searchable' => true, 'orderable' => true],
        ];
    }

    public function getData(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get('start');
        $rowperpage = $request->get('length');

        $columnName_arr = $request->get('columns');
        $search_arr = $request->get('search');

        $defaultColumn = 'mst_siswas.created_at';
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

        if (!$columnName || $columnName == 'no' || $columnName == 'check') {
            $columnName = $defaultColumn;
            $columnSortOrder = $defaultOrder;
        }

        $totalRecords = mst_siswa::select('count(*) as allcount')
            ->count();
        $totalRecordswithFilter = mst_siswa::select('count(*) as allcount')
            ->leftJoin('mst_kelas', 'mst_kelas.id', '=', 'mst_siswas.id_kelas')
            ->whereAny([
                'mst_siswas.no_pendaftaran',
                'mst_siswas.nis',
                'mst_siswas.nisn',
                'mst_siswas.nama',
                'mst_siswas.agama',
                'mst_siswas.tmp_lahir',
                'mst_siswas.tgl_lahir',
                'mst_siswas.jk',
                'mst_siswas.id_kelas',
                'mst_siswas.angkatan',
                'mst_siswas.alamat',
                'mst_siswas.nowa',
                'mst_siswas.email',
            ], 'like', '%' . $searchValue . '%')
            ->count();

        // Fetch records
        $records = mst_siswa::orderBy($columnName, $columnSortOrder)
            ->leftJoin('mst_kelas', 'mst_kelas.id', '=', 'mst_siswas.id_kelas')
            ->leftJoin('mst_thn_aka', 'mst_thn_aka.id', '=', 'mst_siswas.id_thn_aka')
            ->whereAny([
                'mst_siswas.no_pendaftaran',
                'mst_siswas.nis',
                'mst_siswas.nisn',
                'mst_siswas.nama',
                'mst_siswas.agama',
                'mst_siswas.tmp_lahir',
                'mst_siswas.tgl_lahir',
                'mst_siswas.jk',
                'mst_siswas.id_kelas',
                'mst_siswas.angkatan',
                'mst_siswas.alamat',
                'mst_siswas.nowa',
                'mst_siswas.email'
            ], 'like', '%' . $searchValue . '%')
            ->select(
                'mst_siswas.id',
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
            )
            ->skip($start)
            ->take($rowperpage)
            ->get()
            ->map(
                function ($item) {
                    $item->item_id = Crypt::encrypt($item->id);
                    unset($item->id);
                    return $item;
                }
            );

        $data_arr = array();
        $numberStart = $start + 1;
        foreach ($records as $record) {
            $data_arr[] = array(
                'item_id' => $record->item_id,
                'no' => $numberStart,
                'no_pendaftaran' => $record->no_pendaftaran,
                'nis' => $record->nis,
                'nisn' => $record->nisn,
                'nama' => $record->nama,
                'agama' => $record->agama,
                'tmp_lahir' => $record->tmp_lahir,
                'tgl_lahir' => $record->tgl_lahir,
                'kelas' => $record->kelas . ' ' . $record->kelompok,
                'jk' => $record->jk,
                'id_kelas' => $record->id_kelas,
                'id_thn_aka' => $record->id_thn_aka,
                'angkatan' => $record->angkatan,
                'alamat' => $record->alamat,
                'nowa' => $record->nowa,
                'email' => $record->email,
                'nama_ortu' => $record->nama_ortu,
                'thn_aka' => $record->thn_aka,
                'check' => true,
            );

            $numberStart++;
        }
        $response = array(
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecordswithFilter,
            'data' => $data_arr,
        );
        return response()->json($response);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pindah' => ['required', 'in:all,satuan'],
            'ke_kelas' => ['required', 'different:dari_kelas'],
            'dari_kelas' => ['required', 'different:ke_kelas'],
        ], ValidationMessage::messages(), ValidationMessage::attributes());
        $validator->sometimes('siswa', 'required|array|min:1', function ($input) {
            return $input->pindah === 'satuan';
        });
        $valMsg = $request->pindah === 'satuan' ? 'silahkan pilih siswa yang akan dipindahkan kelasnya!' : $validator->errors()->first();
        if ($validator->fails()) return response()->json(['message' => $valMsg, 'error' => $validator->errors()], 422);

        $dariKelas = mst_kelas::where('id', '=', $request->dari_kelas)->first();
        $keKelas = mst_kelas::where('id', '=', $request->ke_kelas)->first();
        if (!$keKelas && $dariKelas) return response()->json(['message' => 'Kelas tidak ditemukan, silahkan muat ulang halaman!'], 422);

        switch ($request->pindah) {
            case 'all':
                $siswas = scctcust::select(['CUSTID'])
                    ->where('CODE03', $request->dari_kelas)
                    ->get();
                if ($siswas->isEmpty()) return response()->json(['message' => 'Tidak ada siswa di kelas dan angkatan ini'], 422);
                break;
            case 'satuan':
                $siswas = scctcust::select(['CUSTID'])->whereIn('CUSTID', $request->input('siswa'))->get();
                if ($siswas->isEmpty()) return response()->json(['message' => 'Siswa tidak ditemukan'], 422);
                if (count($request->input('siswa')) != $siswas->count()) return response()->json(['message' => 'Jumlah siswa yang dipilih tidak sesuai dengan jumlah data, silahkan muat ulang halaman!'], 422);
                break;
            default:
                return response()->json(['message' => 'Data tidak valid, silahkan muat ulang halaman '], 422);
        }

        try {
            DB::beginTransaction();
            scctcust::whereIn('CUSTID', $siswas->pluck('CUSTID'))
                ->update([
                    'DESC02' => $keKelas->jenjang,
                    'CODE02' => $keKelas->unit,
                    'CODE03' => $keKelas->id,
                    'DESC03' => $keKelas->kelas,
                    ]);
            DB::commit();
            return response()->json(['message' => 'Siswa telah dipindahkan dari kelas ' . $dariKelas->kelas . ' ' . $dariKelas->jenjang . ' ke kelas ' . $keKelas->kelas . ' ' . $keKelas->kelompok], 200);
        } catch (\Throwable $e) {
            DB::rollback();
            return response()->json(['message' => 'Pindah kelas gagal dilakukan', 'error' => $e->getMessage()], 422);
        }
    }
}
