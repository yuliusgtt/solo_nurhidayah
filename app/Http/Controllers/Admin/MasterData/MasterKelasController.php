<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
        //        dd($angkatan);
        $data['title'] = $this->title;
        $data['mainTitle'] = $this->mainTitle;
        $data['dataTitle'] = $this->dataTitle;
        $data['modalLink'] = view('admin.master_data.data_siswa.modal', compact('kelas', 'angkatan'));
        $data['columnsUrl'] = route('admin.master-data.data-siswa.get-column');
        $data['datasUrl'] = route('admin.master-data.data-siswa.get-data');

        return view('admin.master_data.data_siswa.index', $data);
    }
}
