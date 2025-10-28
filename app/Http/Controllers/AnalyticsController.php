<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsAlsecoData;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AnalyticsController extends Controller
{
    public function uploadAlseco(Request $request)
    {
        $month = $request->query('month');
        $year  = $request->query('year');

        $month = is_numeric($month) ? (int)$month : null;
        $year  = is_numeric($year) ? (int)$year : null;

        $file = $request->file('file');
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        foreach (array_slice($rows, 5) as $row) {
            if (empty($row['A']) && empty($row['E']) && empty($row['K'])) {
                continue;
            }

            AnalyticsAlsecoData::create([
                'account_number'         => self::str($row['A']),
                'management_code'        => self::str($row['B']),
                'management_name'        => self::str($row['C']),
                'supplier_code'          => self::str($row['D']),
                'supplier_name'          => self::str($row['E']),
                'region'                 => self::str($row['F']),
                'locality'               => self::str($row['G']),
                'locality_part'          => self::str($row['H']),
                'house'                  => self::str($row['I']),
                'apartment'              => self::str($row['J']),
                'full_name'              => self::str($row['K']),
                'people_count'           => self::intOrNull($row['L']),
                'supplier_people_count'  => self::intOrNull($row['M']),
                'area'                   => self::num($row['N']),
                'tariff'                 => self::num($row['O']),
                'service'                => self::str($row['P']),
                'balance_start'          => self::num($row['Q']),
                'balance_change'         => self::num($row['R']),
                'initial_accrual'        => self::num($row['S']),
                'accrual_change'         => self::num($row['T']),
                'accrual_end'            => self::num($row['U']),
                'payment_date'           => self::str($row['V']),
                'payment'                => self::num($row['W']),
                'payment_transfer'       => self::num($row['X']),
                'balance_end'            => self::num($row['Y']),
                'note'                   => self::str($row['Z']),
                'month'                  => $month,
                'year'                   => $year,
            ]);
        }

        return response()->json(['message' => 'Файл успешно импортирован']);
    }

    protected static function str($v): ?string
    {
        if ($v === null) return null;
        $v = str_replace("\xc2\xa0", ' ', (string)$v);
        $v = trim($v);
        return $v === '' ? null : $v;
    }

    protected static function num($v): ?float
    {
        if ($v === null) return null;
        $s = str_replace(["\xc2\xa0", ' '], '', (string)$v);
        $s = str_replace(',', '.', $s);
        return is_numeric($s) ? (float)$s : null;
    }

    protected static function intOrNull($v): ?int
    {
        if ($v === null) return null;
        $s = trim(str_replace(["\xc2\xa0", ' '], '', (string)$v));
        return $s === '' || !is_numeric($s) ? null : (int)$s;
    }
}
