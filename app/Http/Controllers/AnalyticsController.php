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
        $rows = $sheet->toArray(null, true, false, true);

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
        $v = (string)$v;
        $v = str_replace(["\xC2\xA0", "\xE2\x80\xAF", "\xE2\x80\x89"], ' ', $v);
        $v = trim($v);
        return $v === '' ? null : $v;
    }

    protected static function num($v): ?float
    {
        if ($v === null || $v === '') return null;

        if (is_float($v) || is_int($v)) {
            return (float)$v;
        }

        $s = (string)$v;
        $s = str_replace(["\xC2\xA0", "\xE2\x80\xAF", "\xE2\x80\x89", ' '], '', $s);
        $neg = false;
        if (preg_match('/^\((.*)\)$/u', $s, $m)) {
            $s = $m[1];
            $neg = true;
        }

        $s = preg_replace('/[‐-–—−]/u', '-', $s);
        $s = preg_replace('/[^0-9,\.\-]/u', '', $s);
        if (strpos($s, ',') !== false && strpos($s, '.') !== false) {
            $s = str_replace('.', '', $s);
        }

        $s = str_replace(',', '.', $s);
        $s = trim($s);

        if ($neg && $s !== '' && $s[0] !== '-') {
            $s = '-' . $s;
        }

        return is_numeric($s) ? (float)$s : null;
    }

    protected static function intOrNull($v): ?int
    {
        if ($v === null || $v === '') return null;

        if (is_int($v)) return $v;

        $s = (string)$v;
        $s = str_replace(["\xC2\xA0", "\xE2\x80\xAF", "\xE2\x80\x89", ' '], '', $s);
        $neg = false;
        if (preg_match('/^\((.*)\)$/u', $s, $m)) {
            $s = $m[1];
            $neg = true;
        }

        $s = preg_replace('/[‐-–—−]/u', '-', $s);
        $s = preg_replace('/[^0-9\-]/u', '', $s);

        $s = trim($s);
        if ($neg && $s !== '' && $s[0] !== '-') {
            $s = '-' . $s;
        }

        return is_numeric($s) ? (int)$s : null;
    }

    public function periodsByAccount(Request $request)
    {
        $account = trim((string)$request->query('account_number'));

        if ($account === '') {
            return response()->json([
                'message' => 'Укажите query-параметр account_number'
            ], 422);
        }

        $rows = AnalyticsAlsecoData::query()
            ->where('account_number', $account)
            ->whereNotNull('year')
            ->whereNotNull('month')
            ->select(['year', 'month'])
            ->distinct()
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $services = AnalyticsAlsecoData::query()
            ->where('account_number', $account)
            ->whereNotNull('service')
            ->select('service')
            ->distinct()
            ->orderBy('service')
            ->pluck('service')
            ->toArray();

        if ($rows->isEmpty()) {
            return response()->json([
                'account_number' => $account,
                'periods' => [],
                'services' => $services,
            ]);
        }

        $grouped = [];
        foreach ($rows as $r) {
            $y = (int)$r->year;
            $m = (int)$r->month;
            if ($y <= 0 || $m < 1 || $m > 12) {
                continue;
            }
            $grouped[$y][] = $m;
        }

        $periods = [];
        foreach ($grouped as $year => $months) {
            $months = array_values(array_unique($months));
            sort($months, SORT_NUMERIC);
            $periods[] = [
                'year'   => (int)$year,
                'months' => $months,
            ];
        }

        usort($periods, fn($a, $b) => $a['year'] <=> $b['year']);

        return response()->json([
            'account_number' => $account,
            'periods'        => $periods,
            'services'       => $services,
        ]);
    }


    public function monthlyServiceSummary(Request $request)
    {
        $account = trim((string)$request->query('account_number'));
        if ($account === '') {
            return response()->json(['message' => 'Укажите query-параметр account_number'], 422);
        }

        $serviceParam = trim((string)$request->query('service_name', ''));
        $serviceList = [];
        if ($serviceParam !== '') {
            $serviceList = array_values(array_filter(array_map(
                fn($s) => trim($s),
                explode(',', $serviceParam)
            ), fn($s) => $s !== ''));
        }

        $period = trim((string)$request->query('period', ''));
        $ymFrom = null; $ymTo = null;
        if ($period !== '') {
            $parts = array_map('trim', explode('-', $period));
            if (count($parts) !== 2) {
                return response()->json(['message' => 'Неверный формат period. Пример: 9.24 - 3.25'], 422);
            }
            $ymFrom = $this->parseMonthDotYear($parts[0]);
            $ymTo   = $this->parseMonthDotYear($parts[1]);
            if (!$ymFrom || !$ymTo) {
                return response()->json(['message' => 'Неверный формат period. Пример: 9.24 - 3.25'], 422);
            }
            $fromKey = $ymFrom[0] * 100 + $ymFrom[1];
            $toKey   = $ymTo[0]   * 100 + $ymTo[1];
            if ($fromKey > $toKey) {
                [$ymFrom, $ymTo] = [$ymTo, $ymFrom];
            }
        }

        $q = AnalyticsAlsecoData::query()
            ->where('account_number', $account)
            ->whereNotNull('year')
            ->whereNotNull('month');

        if (!empty($serviceList)) {
            $q->whereIn('service', $serviceList);
        }
        if ($ymFrom && $ymTo) {
            $fromKey = $ymFrom[0] * 100 + $ymFrom[1];
            $toKey   = $ymTo[0]   * 100 + $ymTo[1];
            $q->whereRaw('(year * 100 + month) BETWEEN ? AND ?', [$fromKey, $toKey]);
        }

        $rows = $q->select(['year', 'month', 'service'])
            ->selectRaw('SUM(COALESCE(accrual_change, 0)) as monthly_accrual_sum') // заменили initial_accrual на accrual_change
            ->selectRaw('AVG(NULLIF(tariff, 0))            as avg_tariff')
            ->groupBy('year', 'month', 'service')
            ->orderBy('year')->orderBy('month')->orderBy('service')
            ->get();

        $result = [];
        foreach ($rows as $r) {
            $y = (int)$r->year;
            $m = (int)$r->month;

            if (!isset($result[$y])) $result[$y] = [];
            if (!isset($result[$y][$m])) $result[$y][$m] = [];
            $monthlyAccrual = abs((float)$r->monthly_accrual_sum);
            $tariff = $r->avg_tariff !== null ? (float)$r->avg_tariff : null;
            $readings = null;
            if ($tariff !== null && $tariff != 0.0) {
                $readings = abs($monthlyAccrual / $tariff);
            }

            $result[$y][$m][] = [
                'service'         => $r->service,
                'tariff'          => $tariff,
                'monthly_accrual' => $monthlyAccrual,
                'readings'        => $readings,
            ];
        }

        ksort($result, SORT_NUMERIC);
        $payload = [];
        foreach ($result as $year => $months) {
            ksort($months, SORT_NUMERIC);
            $monthsArr = [];
            foreach ($months as $month => $services) {
                $monthsArr[] = [
                    'month'    => (int)$month,
                    'services' => $services,
                ];
            }
            $payload[] = [
                'year'   => (int)$year,
                'months' => $monthsArr,
            ];
        }

        $servicesListOut = AnalyticsAlsecoData::query()
            ->where('account_number', $account)
            ->when(!empty($serviceList), fn($qq) => $qq->whereIn('service', $serviceList))
            ->when(($ymFrom && $ymTo), function ($qq) use ($ymFrom, $ymTo) {
                $fromKey = $ymFrom[0] * 100 + $ymFrom[1];
                $toKey   = $ymTo[0]   * 100 + $ymTo[1];
                $qq->whereRaw('(year * 100 + month) BETWEEN ? AND ?', [$fromKey, $toKey]);
            })
            ->whereNotNull('service')
            ->select('service')
            ->distinct()
            ->orderBy('service')
            ->pluck('service')
            ->toArray();

        return response()->json([
            'account_number' => $account,
            'filters' => [
                'service_name' => $serviceList ?: null,
                'period'       => $period ?: null,
            ],
            'summary'  => $payload,
            'services' => $servicesListOut,
        ]);
    }

    private function parseMonthDotYear(string $s): ?array
    {
        $s = trim($s);
        if (!preg_match('/^(\d{1,2})\.(\d{2}|\d{4})$/', $s, $m)) {
            return null;
        }
        $mth = (int)$m[1];
        $yr  = (int)$m[2];
        if ($mth < 1 || $mth > 12) return null;
        if ($yr < 100) $yr = 2000 + $yr;
        return [$yr, $mth];
    }
}
