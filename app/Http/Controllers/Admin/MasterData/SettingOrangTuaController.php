<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Imports\MasterData\ImportSettingAtributSiswa;
use App\Imports\MasterData\ImportSettingOrangTua;
use App\Models\MasterData\mst_kelas;
use App\Models\MasterData\mst_sekolah;
use App\Models\MasterData\mst_thn_aka;
use App\Models\scctcust;
use App\Models\ValidationMessage;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;
use Maatwebsite\Excel\Validators\ValidationException;

class SettingOrangTuaController extends Controller
{
    public string $title = 'Master Data';
    public string $mainTitle = 'Setting Orang Tua';
    public string $dataTitle = 'Setting Orang Tua';

    public function index()
    {
        $data['title'] = $this->title;
        $data['mainTitle'] = $this->mainTitle;
        $data['dataTitle'] = $this->dataTitle;
        $data['columnsUrl'] = route('admin.master-data.setting-orang-tua.get-column');
        $data['datasUrl'] = route('admin.master-data.setting-orang-tua.get-data');

        return view('admin.master_data.setting_orang_tua.index', $data);
    }

    public function store(Request $request)
    {
        $request->validate(
            [
                'fileImport' => ['required', 'mimes:xls,xlsx', 'max:1024']
            ],
            ValidationMessage::messages(),
            ValidationMessage::attributes()
        );

        $file = $request->fileImport;

        try {
            $headingsData = (new HeadingRowImport)->toArray($file);
            $requiredColumns = ['nis', 'kontakwali'];
            if (empty($headingsData) || !isset($headingsData[0][0])) throw new \Exception ('Tidak dapat membaca judul kolom dari file. Pastikan file memiliki header yang sesuai.');
            $headings = $headingsData[0][0];
            $headings = array_map('strtolower', $headings);
            $missingColumns = [];
            foreach ($requiredColumns as $column) if (!in_array($column, $headings)) $missingColumns[] = $column;

            if (!empty($missingColumns)) {
                $formattedMissingColumns = strtoupper(str_replace('_', ' ', implode(', ', $missingColumns)));
                $formattedRequiredColumns = strtoupper(str_replace('_', ' ', implode(', ', $requiredColumns)));
                throw new Exception ("Kolom $formattedMissingColumns tidak ditemukan.<br><hr> pastikan kolom berikut ada dan terisi pada file import yang akan diproses: $formattedRequiredColumns.",);
            }

            DB::beginTransaction();
            Excel::import(new ImportSettingOrangTua(), $file);
            DB::commit();

            $data = Cache::get('import_setting_orang_tua');
            return response()->json(['message' => 'Sukses, data tagihan telah diimport, silahkan periksa kembali', 'data' => $data], 200);
        } catch (ValidationException $e) {
            $errorMessages = $e->errors();
            $errorMessage = $errorMessages['error'][0] ?? 'Terjadi kesalahan saat melakukan import data.';
            return response()->json(['message' => $errorMessage, 'error' => $errorMessages], 422);
        } catch (Exception $e) {
            $error = $e->getMessage();
            return response()->json(['message' => "Gagal!<br> tidak dapat melakukan $this->mainTitle.<hr> $error", 'error' => $error], 422);
        }
    }

    public function getColumn()
    {
        return [
            ['data' => null, 'name' => 'no', 'className' => 'text-center', 'columnType' => 'row'],
            ['data' => 'nis', 'name' => 'NIS', 'searchable' => true, 'orderable' => true],
            ['data' => 'NOVA', 'name' => 'NO VA'],
            ['data' => 'name', 'name' => 'NAMA', 'searchable' => true, 'orderable' => true],
            ['data' => 'ortu', 'name' => 'WALI', 'searchable' => true, 'orderable' => true],
            ['data' => 'kontakwali', 'name' => 'Kontak Wali', 'searchable' => true, 'orderable' => true],
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

        $cacheKey = 'import_setting_orang_tua';
        $cachedData = Cache::get($cacheKey, []);


        $nisList = collect($cachedData)->pluck('nis')->toArray();
        $nisCount = count($cachedData);
        $students = scctcust::whereIn('scctcust.NOCUST', $nisList)->get()->toArray();

//        dd($cachedData);


        $whereAny = [
            'scctcust.NMCUST',
            'scctcust.NOCUST',
        ];

        $select = array_unique(array_merge($whereAny, [
            'scctcust.NUM2ND',
            'scctcust.CODE02',
            'scctcust.DESC02',
            'scctcust.DESC03',
            'scctcust.DESC04',

        ]));

        $records = collect($cachedData)->map(function ($item) use ($students) {
            $nis = $item['nis'];
            $index = array_search($nis, array_column($students, 'NOCUST'));
            return [
                'nis' => $nis,
                'name' => $students[$index]['NMCUST'] ?? null,
                'ortu' => $students[$index]['GENUS'] ?? null,
                'kontakwali' => $item['kontakwali'],
            ];
        });

        $response = array(
            'draw' => intval($draw),
            'recordsTotal' => $nisCount,
            'recordsFiltered' => $nisCount,
            'data' => $records,
        );
        return response()->json($response);
    }

    public function validateData()
    {

        $data = Cache::get('import_setting_orang_tua');
        if (is_null($data) || (is_array($data) && empty($data))) return response()->json(['message' => 'Tidak ada data yang dapat diproses, silahkan upload file terlebih dahulu'], 422);
        foreach ($data as $item) {
            if (strlen($item['nis']) <= 10 && $item['kontakwali']) {
                $existingCust = scctcust::where('NOCUST', $item['nis'])->first();
                $existingCust?->update([
                    'GENUSContact' => $item['kontakwali'],
                ]);
            }
        }

        Cache::forget('import_setting_orang_tua');

        return response()->json(['message' => 'Sukses, data setting orang tua telah disimpan'], 200);
    }
}
