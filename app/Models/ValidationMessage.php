<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ValidationMessage extends Model
{
    public static function attributes(): array
    {
        return [
            'name' => 'Nama',
            'user_id' => 'Anggota',
            'kredit' => 'Nominal',
            'tenor' => 'Tenor',
            'nomor_induk_pegawai' => 'Nomor Induk Pegawai',
            'tgl_lahir' => 'Tanggal Lahir',
            'tmp_lahir' => 'Tempat Lahir',
            'pendidikan' => 'Pendidikan',
            'Alamat' => 'Alamat',
            'nowa' => 'Nomor Whatsapp',
            'fileImport' => 'File',
            'kelas' => 'kelas',
            'kelompok' => 'kelompok',
            'unit' => 'unit',
            'kode' => 'kode',
            'code' => 'kode',
            'nis' => 'Nomor Induk Siswa',
            'no_pendaftaran' => 'Nomor Pendaftaran',
            'id_kelas' => 'Kelas',
            'angkatan' => 'Angkatan',
            'nama' => 'Nama',
            'siswa' => 'Siswa',
            'ke_kelas' => 'Kelas tujuan',
            'dari_kelas' => 'Kelas asal',
            'thn_aka' => 'Tahun Akademik/Pelajaran',
        ];
    }

    public static function messages(): array
    {
        return [
            'email' => ':attribute harus berupa email',
            'different' => ':attribute dan :attribute harus berbeda.',
            'required' => ':attribute harus diisi',
            'required_if' => 'Pilih :attribute',
            'integer' => ':attribute harus berisi angka',
            'string' => ':attribute harus berisi huruf &/ angka',
            'regex' => 'Format :attribute salah',
            'unique' => ':attribute sudah digunakan',
            'mimes' => ':attribute harus bertipe: :values',
            'file.max' => ':attribute terlalu besar (maksimal :max kilobytes)',
            'file.min' => ':attribute terlalu kecil (minimal :min kilobytes)',
            'file.required' => ':attribute harus diisi',
            'uploaded' => ':attribute tidak sesuai ketentuan',
            'max' => [
                'numeric' => ':attribute terlalu besar (maksimal :max karakter)',
            ],
            'min' => [
                'numeric' => ':attribute terlalu kecil (minimal :min karakter)',
            ],
            'in' => ':attribute tidak valid',
            'not_in' => ':attribute tidak valid',
        ];
    }
}
