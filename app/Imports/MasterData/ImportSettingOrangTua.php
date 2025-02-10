<?php

namespace App\Imports\MasterData;

use App\Models\scctcust;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ImportSettingOrangTua implements ToCollection, WithHeadingRow
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection): void
    {
        $cacheKey = 'import_setting_orang_tua';
        $data = [];

        foreach ($collection as $row) {
            if (isset($row['nis']) && isset($row['kontakwali'])) {
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
