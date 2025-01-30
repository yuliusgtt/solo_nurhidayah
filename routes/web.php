<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [AuthController::class, 'index'])->name('index');
Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/reload-captcha', [AuthController::class, 'reloadCaptcha'])->name('reload-captcha');

Route::prefix('admin')->name('admin.')->middleware('check.session')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');

    Route::prefix('master-data')->name('master-data.')->group(function () {
        Route::prefix('master-kelas')->name('master-kelas.')->controller(\App\Http\Controllers\Admin\MasterData\MasterKelasController::class)->group(function () {
            Route::get('get-data', 'getData')->name('get-data');
            Route::get('get-column', 'getColumn')->name('get-column');
        });
        Route::resource('master-kelas', \App\Http\Controllers\Admin\MasterData\MasterKelasController::class)->names('master-kelas');

        Route::prefix('tahun-pelajaran')->name('tahun-pelajaran.')->controller(\App\Http\Controllers\Admin\MasterData\TahunPelajaranController::class)->group(function () {
            Route::get('get-data', 'getData')->name('get-data');
            Route::get('get-column', 'getColumn')->name('get-column');
        });
        Route::resource('tahun-pelajaran', \App\Http\Controllers\Admin\MasterData\TahunPelajaranController::class)->names('tahun-pelajaran');

        Route::prefix('master-post')->name('master-post.')
            ->controller(\App\Http\Controllers\Admin\MasterData\MasterPostController::class)->group(function () {
                Route::get('get-data', 'getData')->name('get-data');
                Route::get('get-column', 'getColumn')->name('get-column');
                Route::resource('', \App\Http\Controllers\Admin\MasterData\MasterPostController::class)->parameters(['' => 'id']);
            });

        Route::resource('beban-post', \App\Http\Controllers\Admin\MasterData\BebanPostController::class)->names('beban-post');
        Route::resource('export-import-data', \App\Http\Controllers\Admin\MasterData\ExportImportDataController::class)->names('export-import-data');

        Route::prefix('setting-atribut-siswa')->name('setting-atribut-siswa.')
            ->controller(\App\Http\Controllers\Admin\MasterData\SettingAtributSiswaController::class)->group(function () {
//                Route::get('get-data', 'getData')->name('get-data');
//                Route::get('get-column', 'getColumn')->name('get-column');
                Route::resource('', \App\Http\Controllers\Admin\MasterData\SettingAtributSiswaController::class)->parameters(['' => 'id']);
            });

        Route::prefix('setting-orang-tua')->name('setting-orang-tua.')
            ->controller(\App\Http\Controllers\Admin\MasterData\SettingOrangTuaController::class)->group(function () {
//                Route::get('get-data', 'getData')->name('get-data');
//                Route::get('get-column', 'getColumn')->name('get-column');
                Route::resource('', \App\Http\Controllers\Admin\MasterData\SettingOrangTuaController::class)->parameters(['' => 'id']);
            });


        Route::prefix('data-siswa')->name('data-siswa.')->controller(\App\Http\Controllers\Admin\MasterData\DataSiswaController::class)->group(function () {
            Route::get('get-data', 'getData')->name('get-data');
            Route::get('get-column', 'getColumn')->name('get-column');
        });
        Route::resource('data-siswa', \App\Http\Controllers\Admin\MasterData\DataSiswaController::class)->names('data-siswa');
    });


    Route::prefix('keuangan')->name('keuangan.')->group(function () {
        Route::prefix('tagihan-siswa')->name('tagihan-siswa.')->group(function () {
            Route::prefix('buat-tagihan')->name('buat-tagihan.')->group(function () {
                Route::get('get-data', [\App\Http\Controllers\Admin\Keuangan\TagihanSiswa\BuatTagihanController::class, 'getData'])->name('get-data');
                Route::get('get-siswa', [\App\Http\Controllers\Admin\Keuangan\TagihanSiswa\BuatTagihanController::class, 'getSiswa'])->name('get-siswa');
                Route::get('get-column', [\App\Http\Controllers\Admin\Keuangan\TagihanSiswa\BuatTagihanController::class, 'getColumn'])->name('get-column');
                Route::resource('', \App\Http\Controllers\Admin\Keuangan\TagihanSiswa\BuatTagihanController::class)->parameters(['' => 'id']);

            });

            Route::prefix('data-tagihan')->name('data-tagihan.')->group(function () {
                Route::controller(\App\Http\Controllers\Admin\Keuangan\TagihanSiswa\DataTagihanController::class)->group(function () {
                    Route::get('get-data', 'getData')->name('get-data');
                    Route::get('get-column', 'getColumn')->name('get-column');
                    Route::get('cetak-rekap', 'cetak')->name('cetak-rekap');
                    Route::resource('', \App\Http\Controllers\Admin\Keuangan\TagihanSiswa\DataTagihanController::class)->parameters(['' => 'id']);
                });
            });
        });

        Route::prefix('penerimaan-siswa')->name('penerimaan-siswa.')->group(function () {
            Route::prefix('data-penerimaan')->name('data-penerimaan.')->group(function () {
                Route::controller(\App\Http\Controllers\Admin\Keuangan\PenerimaanSiswa\DataPenerimaanController::class)->group(function () {
                    Route::get('get-data', 'getData')->name('get-data');
                    Route::get('get-column', 'getColumn')->name('get-column');
                    Route::get('cetak-rekap', 'cetak')->name('cetak-rekap');
                    Route::get('cetak-tagihan-dibayar', 'cetakPembayaran')->name('cetak-tagihan-dibayar');
                    Route::resource('', \App\Http\Controllers\Admin\Keuangan\PenerimaanSiswa\DataPenerimaanController::class)->parameters(['' => 'id']);
                });
            });
        });
    });


});
