<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingAtributSiswaController extends Controller
{
    public string $title = 'Master Data';
    public string $mainTitle = 'Setting Atribut Siswa';
    public string $dataTitle = 'Setting Atribut Siswa';

    public function index()
    {
        $data['title'] = $this->title;
        $data['mainTitle'] = $this->mainTitle;
        $data['dataTitle'] = $this->dataTitle;

        return view('admin.master_data.setting_attribut_siswa.index', $data);
    }
}
