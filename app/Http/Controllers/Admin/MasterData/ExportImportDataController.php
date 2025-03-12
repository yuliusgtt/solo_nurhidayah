<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Imports\MasterData\ImportDataSiswa;
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

class ExportImportDataController extends Controller
{
    public string $title = 'Master Data';
    public string $mainTitle = 'Export Import Data';
    public string $dataTitle = 'Export Import Data';

    public function index()
    {
        $data['title'] = $this->title;
        $data['mainTitle'] = $this->mainTitle;
        $data['dataTitle'] = $this->dataTitle;
        $data['columnsUrl'] = route('admin.master-data.export-import-data.get-column');
        $data['datasUrl'] = route('admin.master-data.export-import-data.get-data');

        return view('admin.master_data.export_import_data.index', $data);
    }

    public function getColumn()
    {
        return [
            ['data' => null, 'name' => 'no', 'className' => 'text-center', 'columnType' => 'row'],
            ['data' => 'nis', 'name' => 'NIS', 'searchable' => true, 'orderable' => true],
//            ['data' => 'NOVA', 'name' => 'NO VA'],
            ['data' => 'name', 'name' => 'NAMA', 'searchable' => true, 'orderable' => true],
            ['data' => 'kelas', 'name' => 'Kelas', 'searchable' => true, 'orderable' => true],
            ['data' => 'kelompok', 'name' => 'Kelompok', 'searchable' => true, 'orderable' => true],
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

        $cacheKey = 'import_data_siswa';
        $cachedData = Cache::get($cacheKey, []);


        $nisList = collect($cachedData)->pluck('nis')->toArray();
        $nisCount = count($cachedData);

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

        $records = collect($cachedData)->map(function ($item) {
            $nis = $item['nis'];return [
                'nis' => $nis,
                'name' => $item['nama'] ?? null,
                'ortu' => $item['ayah'] ?? null,
                'kelas' => $item['kelas'] ?? null,
                'kelompok' => $item['kelompok'] ?? null,
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
            $requiredColumns = ['nis', 'nama', 'nodaf', 'unit', 'kelas', 'kelompok', 'angkatan', 'ayah', 'ibu', 'eksint', 'kontakwali', 'wisma'];
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
            Excel::import(new ImportDataSiswa(), $file);
            DB::commit();

            $data = Cache::get('import_data_siswa');
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

    public function validateData()
    {

        $data = Cache::get('import_data_siswa');
        if (is_null($data) || (is_array($data) && empty($data))) return  response()->json(['message' => 'Tidak ada data yang dapat diproses, silahkan upload file terlebih dahulu'], 422);
        foreach ($data as $item) {
//            dd($item);
            $thn_aka = mst_thn_aka::where('thn_aka', $item['angkatan'])->first();
            $sekolah = mst_sekolah::where('DESC01', $item['unit'])->first();
            $kelas = mst_kelas::where('unit', $item['unit'])
                ->where('jenjang', $item['kelas'])
                ->where('kelas', $item['kelompok'])
                ->first();

            if (strlen($item['nis']) <= 10 && $sekolah && $kelas && $thn_aka) {
                $existingCust = scctcust::where('NOCUST', $item['nis'])->first();
                if (!$existingCust) {
                    scctcust::create([
                        'NOCUST' => $item['nis'],
                        'NMCUST' => $item['nama'],
                        'NUM2ND' => $item['nodaf'],
                        'STCUST' => 1,
                        'CODE01' => $sekolah->CODE01,
                        'DESC01' => 'Nur Hidayah',
                        'CODE02' => $sekolah->DESC01,
                        'DESC02' => $kelas->jenjang,
                        'CODE03' => $kelas->id,
                        'DESC03' => $kelas->kelas,
                        'CODE04' => null,
                        'DESC04' => $thn_aka->thn_aka,
                        'CODE05' => null,
                        'DESC05'=> null,
                        'TOTPAY' => null,
                        'GENUS' => $item['ayah'],
                        'GENUS1' => $item['ibu'],
                        'LastUpdate' => Carbon::now(),
                        'GetWisma' => $item['wisma'],
                        'GENUSContact'=> $item['kontakwali'],
                        'EksternalInternal'=> $item['eksint'],
                    ]);
                }
            }
        }

        Cache::forget('import_data_siswa');

        return response()->json(['message' => 'Sukses, data siswa telah disimpan, silahkan periksa kembali'], 200);
    }
}
