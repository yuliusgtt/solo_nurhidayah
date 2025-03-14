<?php

namespace App\Imports\Keuangan\TagihanSiswa;

use App\Models\scctcust;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ImportTagihanPMBExcel implements ToCollection, WithHeadingRow
{
    public function collection(Collection $collection): void
    {
        $cacheKey = 'import_tagihan_pmb_excel';
        $data = [];

        foreach ($collection as $row) {
            if (isset($row['nodaf']) && isset($row['nominal'])) {
                $row['nodaf'] = (string) $row['nodaf'];
                $checkData = scctcust::where('NUM2ND', $row['nodaf'])->first();
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
