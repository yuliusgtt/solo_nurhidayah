<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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

        return view('admin.master_data.setting_orang_tua.index', $data);
    }
}
