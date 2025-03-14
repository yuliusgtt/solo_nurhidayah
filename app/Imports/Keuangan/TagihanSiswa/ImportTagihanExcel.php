<?php

namespace App\Imports\Keuangan\TagihanSiswa;

use App\Models\scctcust;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ImportTagihanExcel implements ToCollection, WithHeadingRow
{
    /**
    * @param Collection $collection
    */

    public function collection(Collection $collection): void
    {
        $cacheKey = 'import_tagihan_excel';
        $data = [];

        foreach ($collection as $row) {
            if (isset($row['nis']) && isset($row['nominal'])) {
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
