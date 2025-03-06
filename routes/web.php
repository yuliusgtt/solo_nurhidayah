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

        Route::prefix('beban-post')->name('beban-post.')
            ->controller(\App\Http\Controllers\Admin\MasterData\BebanPostController::class)->group(function () {
                Route::get('get-data', 'getData')->name('get-data');
                Route::get('get-column', 'getColumn')->name('get-column');
                Route::resource('', \App\Http\Controllers\Admin\MasterData\BebanPostController::class)->parameters(['' => 'id']);
            });

        Route::prefix('export-import-data')->name('export-import-data.')
            ->controller(\App\Http\Controllers\Admin\MasterData\ExportImportDataController::class)->group(function () {
                Route::get('get-data', 'getData')->name('get-data');
                Route::get('get-column', 'getColumn')->name('get-column');
                Route::post('validate-data', 'validateData')->name('validate-data');
                Route::resource('', \App\Http\Controllers\Admin\MasterData\ExportImportDataController::class)->parameters(['' => 'id']);
            });

        Route::prefix('setting-atribut-siswa')->name('setting-atribut-siswa.')
            ->controller(\App\Http\Controllers\Admin\MasterData\SettingAtributSiswaController::class)->group(function () {
                Route::get('get-data', 'getData')->name('get-data');
                Route::get('get-column', 'getColumn')->name('get-column');
                Route::resource('', \App\Http\Controllers\Admin\MasterData\SettingAtributSiswaController::class)->parameters(['' => 'id']);
            });

        Route::prefix('setting-orang-tua')->name('setting-orang-tua.')
            ->controller(\App\Http\Controllers\Admin\MasterData\SettingOrangTuaController::class)->group(function () {
                Route::get('get-data', 'getData')->name('get-data');
                Route::get('get-column', 'getColumn')->name('get-column');
                Route::resource('', \App\Http\Controllers\Admin\MasterData\SettingOrangTuaController::class)->parameters(['' => 'id']);
            });


        Route::prefix('data-siswa')->name('data-siswa.')->controller(\App\Http\Controllers\Admin\MasterData\DataSiswaController::class)->group(function () {
            Route::get('get-data', 'getData')->name('get-data');
            Route::get('get-column', 'getColumn')->name('get-column');
            Route::get('get-siswa-select2', 'getSiswaSelect2')->name('get-siswa-select2');
        });
        Route::resource('data-siswa', \App\Http\Controllers\Admin\MasterData\DataSiswaController::class)->names('data-siswa');
    });


    Route::prefix('keuangan')->name('keuangan.')->group(function () {
        Route::controller(\App\Http\Controllers\Admin\Keuangan\ManualPembayaranController::class)
            ->prefix('manual-pembayaran')->name('manual-pembayaran.')->group(function () {
                Route::get('get-data', 'getData')->name('get-data');
                Route::get('get-column', 'getColumn')->name('get-column');
                Route::get('get-tagihan', 'getTagihan')->name('get-tagihan');
                Route::get('cetak-tagihan', 'cetakTagihan')->name('cetak-tagihan');
                Route::get('cetak-tagihan-dibayar', 'cetakPembayaran')->name('cetak-tagihan-dibayar');
                Route::resource('', \App\Http\Controllers\Admin\Keuangan\ManualPembayaranController::class)->parameters(['' => 'id']);
            });

        Route::controller(\App\Http\Controllers\Admin\Keuangan\ManualPembayaranNisController::class)
            ->prefix('manual-pembayaran-nis')->name('manual-pembayaran-nis.')->group(function () {
                Route::resource('', \App\Http\Controllers\Admin\Keuangan\ManualPembayaranNisController::class)->parameters(['' => 'id']);
            });

        Route::controller(\App\Http\Controllers\Admin\Keuangan\ManualPembayaranNoPendaftaranController::class)
            ->prefix('manual-pembayaran-no-pendaftaran')->name('manual-pembayaran-no-pendaftaran.')->group(function () {
                Route::resource('', \App\Http\Controllers\Admin\Keuangan\ManualPembayaranNoPendaftaranController::class)->parameters(['' => 'id']);
            });

        Route::prefix('tagihan-siswa')->name('tagihan-siswa.')->group(function () {
            Route::prefix('buat-tagihan')->name('buat-tagihan.')->group(function () {
                Route::get('get-data', [\App\Http\Controllers\Admin\Keuangan\TagihanSiswa\BuatTagihanController::class, 'getData'])->name('get-data');
                Route::get('get-siswa', [\App\Http\Controllers\Admin\Keuangan\TagihanSiswa\BuatTagihanController::class, 'getSiswa'])->name('get-siswa');
                Route::get('get-master-harga', [\App\Http\Controllers\Admin\Keuangan\TagihanSiswa\BuatTagihanController::class, 'getMasterHarga'])->name('get-master-harga');
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

        Route::prefix('saldo')->name('saldo.')->controller(\App\Http\Controllers\Admin\Keuangan\Saldo\SaldoVirtualAccountController::class)->group(function () {
            Route::prefix('saldo-virtual-account')->name('saldo-virtual-account.')->group(function () {
                Route::get('get-data', 'getData')->name('get-data');
                Route::get('get-column', 'getColumn')->name('get-column');
                Route::get('get-saldo', 'getSaldo')->name('get-saldo');
                Route::post('tarik', 'tarik')->name('tarik');
                Route::prefix('transaksi')->name('transaksi.')->group(function () {
                    Route::get('get-data', 'getDataTran')->name('get-data');
                    Route::get('get-column', 'getColumnTran')->name('get-column');
                });
            });
            Route::resource('saldo-virtual-account', \App\Http\Controllers\Admin\Keuangan\Saldo\SaldoVirtualAccountController::class)->names('saldo-virtual-account');

            Route::prefix('transaksi')->name('transaksi.')->controller(\App\Http\Controllers\Admin\Keuangan\Saldo\SccttranController::class)->group(function () {
                Route::get('get-data', 'getData')->name('get-data');
                Route::get('get-column', 'getColumn')->name('get-column');
            });
            Route::resource('transaksi', \App\Http\Controllers\Admin\Keuangan\Saldo\SccttranController::class)->names('transaksi');

        });
    });


});
