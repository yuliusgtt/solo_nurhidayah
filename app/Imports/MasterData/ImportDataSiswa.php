<?php

namespace App\Imports\MasterData;

use App\Models\MasterData\mst_kelas;
use App\Models\scctcust;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ImportDataSiswa implements ToCollection, WithHeadingRow
{
    public function collection(Collection $collection): void
    {
        $cacheKey = 'import_data_siswa';

        $requiredKeys = ['nis', 'nama', 'nodaf', 'unit', 'kelas', 'kelompok', 'angkatan', 'alamat', 'ayah', 'ibu', 'eksint', 'kontakwali', 'wisma'];

        $data = $collection->filter(function ($row) use ($requiredKeys) {
            // Ensure $row is an array
            $row = (array)$row;

            // Check if all required keys exist
            if (count(array_intersect_key(array_flip($requiredKeys), $row)) === count($requiredKeys)) {
                $nis = (string)($row['nis'] ?? '');
                $nodaf = (string)($row['nodaf'] ?? '');

                $kelas = mst_kelas::where('kelas', $row['kelompok'])
                    ->where('jenjang', $row['kelas'])
                    ->where('unit', $row['unit'])->exists();
                if (!$kelas) {
                    return false;
                }

                if (!$nis && $nodaf) {
                    return !scctcust::where('NUM2ND', $nodaf)->exists();
                } else {
                    return !scctcust::where('NOCUST', $nis)->exists();
                }
            }



            return true;
        });

        if ($data->isNotEmpty()) {
            Cache::put($cacheKey, $data->toArray(), now()->addMinutes(60));
        }
    }

    public function headingRow(): int
    {
        return 1;
    }
}
