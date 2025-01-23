<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
//        $data['modalLink'] = view('admin.master_data.data_siswa.modal', compact('kelas', 'angkatan'));
        $data['columnsUrl'] = route('admin.master-data.master-post.get-column');
        $data['datasUrl'] = route('admin.master-data.master-post.get-data');

        return view('admin.master_data.beban_post.index', $data);
    }
}
