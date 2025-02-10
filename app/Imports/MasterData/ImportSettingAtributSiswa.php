<?php

namespace App\Imports\MasterData;

use App\Models\scctcust;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Cache;

class ImportSettingAtributSiswa implements ToCollection, WithHeadingRow
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection): void
    {
        $cacheKey = 'import_setting_atribut_siswa';
        $data = [];

        foreach ($collection as $row) {
            if (isset($row['nis']) && isset($row['wisma'])) {
                $row['nis'] = (string) $row['nis'];
                $checkData = scctcust::where('NOCUST', $row['nis'])->first();
                if ($checkData) {
                    $data[] = $row;
                }
            }
        }

        if (!empty($data)) {
            Cache::put($cacheKey, $data, now()->addMinutes(60));
        }
    }

    public function headingRow(): int
    {
        return 1;
    }
}
