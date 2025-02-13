<?php

namespace App\Http\Controllers\Admin\Keuangan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ManualPembayaranNisController extends Controller
{
    public function __construct()
    {
        $this->title = 'Keuangan';
        $this->mainTitla = 'Manual Pembayaran Nis';
        $this->dataTitle = 'Manual Pembayaran Nis';
        $this->showTitle = 'Detail Manual Pembayaran Nis';


        $this->datasUrl = route('admin.keuangan.manual-pembayaran.get-data');
        $this->detailDatasUrl = '';
        $this->columnsUrl = route('admin.keuangan.manual-pembayaran.get-column');
    }

    public function index()
    {
        $data['title'] = $this->title;
        $data['mainTitle'] = $this->mainTitla;
        $data['dataTitle'] = $this->dataTitle;
        $data['showTitle'] = $this->showTitle;
        $data['columnsUrl'] = $this->columnsUrl;
        $data['thn_aka'] = \App\Models\MasterData\mst_thn_aka::select(['thn_aka'])->where('thn_aka', '!=', null)->get();

        $data['datasUrl'] = $this->datasUrl;
//        $data['thn_aka'] = mst_thn_aka::where('thn_aka', '!=', null)->get();
//        $data['kelas'] = mst_kelas::get();


        return view('admin.keuangan.manual_pembayaran_nis', $data);
    }

}
