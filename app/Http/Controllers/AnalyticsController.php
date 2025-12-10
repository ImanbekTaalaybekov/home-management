<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsAlsecoData;
use App\Models\DebtNameTranslation;
use App\Models\User;
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

        if ($month === null || $year === null) {
            return response()->json([
                'message' => 'Нужно передать query-параметры month и year (числа)',
            ], 422);
        }

        $file = $request->file('file');
        if (!$file) {
            return response()->json([
                'message' => 'Нужно передать файл в поле "file" (form-data, key=file, type=file)',
            ], 422);
        }

        try {
            @set_time_limit(0);

            $existingKeys = AnalyticsAlsecoData::query()
                ->where('month', $month)
                ->where('year', $year)
                ->get(['account_number', 'service'])
                ->map(function ($row) {
                    return $row->account_number.'|'.$row->service;
                })
                ->all();

            $seen = [];
            foreach ($existingKeys as $k) {
                if ($k !== null && $k !== '') {
                    $seen[$k] = true;
                }
            }

            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rowIterator = $sheet->getRowIterator();

            $inserted = 0;

            foreach ($rowIterator as $row) {
                $rowIndex = $row->getRowIndex();

                if ($rowIndex <= 5) {
                    continue;
                }

                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);

                $data = [];
                foreach ($cellIterator as $cell) {
                    $col = $cell->getColumn();
                    $data[$col] = $cell->getValue();
                }

                if (
                    (empty($data['A']) || $data['A'] === null) &&
                    (empty($data['E']) || $data['E'] === null) &&
                    (empty($data['K']) || $data['K'] === null)
                ) {
                    continue;
                }

                $accountNumber = self::str($data['A'] ?? null);
                $service       = self::str($data['P'] ?? null);
                $localityPart  = self::str($data['H'] ?? null);

                if ($accountNumber === null || $accountNumber === '' ||
                    $service === null || $service === '') {
                    continue;
                }

                $key = $accountNumber.'|'.$service;

                if (isset($seen[$key])) {
                    continue;
                }

                $seen[$key] = true;

                AnalyticsAlsecoData::create([
                    'account_number'         => $accountNumber,
                    'management_code'        => self::str($data['B'] ?? null),
                    'management_name'        => self::str($data['C'] ?? null),
                    'supplier_code'          => self::str($data['D'] ?? null),
                    'supplier_name'          => self::str($data['E'] ?? null),
                    'region'                 => self::str($data['F'] ?? null),
                    'locality'               => self::str($data['G'] ?? null),
                    'locality_part'          => $localityPart,
                    'house'                  => self::str($data['I'] ?? null),
                    'apartment'              => self::str($data['J'] ?? null),
                    'full_name'              => self::str($data['K'] ?? null),
                    'people_count'           => self::intOrNull($data['L'] ?? null),
                    'supplier_people_count'  => self::intOrNull($data['M'] ?? null),
                    'area'                   => self::num($data['N'] ?? null),
                    'tariff'                 => self::num($data['O'] ?? null),
                    'service'                => $service,
                    'balance_start'          => self::num($data['Q'] ?? null),
                    'balance_change'         => self::num($data['R'] ?? null),
                    'initial_accrual'        => self::num($data['S'] ?? null),
                    'accrual_change'         => self::num($data['T'] ?? null),
                    'accrual_end'            => self::num($data['U'] ?? null),
                    'payment_date'           => self::str($data['V'] ?? null),
                    'payment'                => self::num($data['W'] ?? null),
                    'payment_transfer'       => self::num($data['X'] ?? null),
                    'balance_end'            => self::num($data['Y'] ?? null),
                    'note'                   => self::str($data['Z'] ?? null),
                    'month'                  => $month,
                    'year'                   => $year,
                ]);

                $inserted++;
            }

            return response()->json([
                'message'  => 'Файл успешно импортирован',
                'inserted' => $inserted,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Alseco upload error: '.$e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'message' => 'Ошибка при обработке файла',
                'error'   => app()->environment('local') ? $e->getMessage() : null,
            ], 500);
        }
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

    public function periodsByAccountTest(Request $request)
    {
        $account = trim((string)$request->query('account_number'));

        if ($account === '') {
            return response()->json([
                'message' => 'Укажите query-параметр account_number'
            ], 422);
        }

        $user = User::where('personal_account', $account)->first();
        $lang = strtolower($user->language ?? 'ru');
        $allowed = ['ru','kg','uz','kk','en','es','zh'];
        if (!in_array($lang, $allowed, true)) {
            $lang = 'ru';
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

        $servicesOriginal = AnalyticsAlsecoData::query()
            ->where('account_number', $account)
            ->whereNotNull('service')
            ->select('service')
            ->distinct()
            ->orderBy('service')
            ->pluck('service')
            ->toArray();

        $translations = DebtNameTranslation::query()
            ->whereIn('original', $servicesOriginal)
            ->get()
            ->keyBy('original');

        $translate = function (string $orig) use ($translations, $lang) {
            $t = $translations->get($orig);
            if ($t && !empty($t->{$lang})) return $t->{$lang};
            if ($t && !empty($t->ru))      return $t->ru;
            return $orig;
        };

        $services = [];
        foreach ($servicesOriginal as $orig) {
            $services[] = [
                'original'   => $orig,
                'translated' => $translate($orig),
            ];
        }

        if ($rows->isEmpty()) {
            return response()->json([
                'account_number' => $account,
                'periods'        => [],
                'services'       => $services,
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



    public function monthlyServiceSummaryTest(Request $request)
    {
        $account = trim((string)$request->query('account_number'));
        if ($account === '') {
            return response()->json(['message' => 'Укажите query-параметр account_number'], 422);
        }

        $user = User::where('personal_account', $account)->first();
        $lang = strtolower($user->language ?? 'ru');
        $allowed = ['ru','kg','uz','kk','en','es','zh'];
        if (!in_array($lang, $allowed, true)) {
            $lang = 'ru';
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

        $base = AnalyticsAlsecoData::query()
            ->where('account_number', $account)
            ->whereNotNull('year')
            ->whereNotNull('month');

        if ($ymFrom && $ymTo) {
            $fromKey = $ymFrom[0] * 100 + $ymFrom[1];
            $toKey   = $ymTo[0]   * 100 + $ymTo[1];
            $base->whereRaw('(year * 100 + month) BETWEEN ? AND ?', [$fromKey, $toKey]);
        }

        $serviceOriginalFilter = [];
        if (!empty($serviceList)) {
            $trs = DebtNameTranslation::query()
                ->where(function($q) use ($serviceList) {
                    $q->whereIn('original', $serviceList)
                        ->orWhereIn('ru', $serviceList)
                        ->orWhereIn('kg', $serviceList)
                        ->orWhereIn('uz', $serviceList)
                        ->orWhereIn('kk', $serviceList)
                        ->orWhereIn('en', $serviceList)
                        ->orWhereIn('es', $serviceList)
                        ->orWhereIn('zh', $serviceList);
                })
                ->get();

            $serviceOriginalFilter = array_unique(array_merge(
                $trs->pluck('original')->all(),
                $serviceList
            ));

            $base->whereIn('service', $serviceOriginalFilter);
        }

        $rows = (clone $base)
            ->select(['year', 'month', 'service'])
            ->selectRaw('SUM(COALESCE(accrual_change, 0))  as monthly_accrual_sum')
            ->selectRaw('AVG(NULLIF(tariff, 0))           as avg_tariff')
            ->groupBy('year', 'month', 'service')
            ->orderBy('year')->orderBy('month')->orderBy('service')
            ->get();

        $servicesUsed = $rows->pluck('service')->unique()->values()->all();

        $servicesOriginalOut = (clone $base)
            ->whereNotNull('service')
            ->select('service')
            ->distinct()
            ->orderBy('service')
            ->pluck('service')
            ->toArray();

        $allServicesToTranslate = array_values(array_unique(array_merge($servicesUsed, $servicesOriginalOut)));
        $translations = DebtNameTranslation::query()
            ->whereIn('original', $allServicesToTranslate)
            ->get()
            ->keyBy('original');

        $translate = function(string $orig) use ($translations, $lang) {
            $t = $translations->get($orig);
            if ($t && !empty($t->{$lang})) return $t->{$lang};
            if ($t && !empty($t->ru))      return $t->ru;
            return $orig;
        };

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
                'service'         => $translate($r->service),
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

        $servicesOut = [];
        foreach ($servicesOriginalOut as $orig) {
            $servicesOut[] = [
                'original'   => $orig,
                'translated' => $translate($orig),
            ];
        }

        return response()->json([
            'account_number' => $account,
            'filters' => [
                'service_name' => !empty($serviceList) ? $serviceList : null,
                'period'       => $period ?: null,
            ],
            'summary'  => $payload,
            'services' => $servicesOut,
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
