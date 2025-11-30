<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsAlsecoData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DebtAnalyticsAdminController extends Controller
{
    protected function buildBaseQuery(Request $request)
    {
        $admin = Auth::guard('sanctum')->user();

        if (!$admin) {
            abort(response()->json(['message' => 'Unauthenticated'], 401));
        }

        if (!$admin->client_id) {
            abort(response()->json(['message' => 'У админа не указан client_id'], 403));
        }

        $residentialComplexId = $request->query('residential_complex_id');
        $serviceFilter        = trim((string)$request->query('service', ''));

        $query = AnalyticsAlsecoData::query()
            ->leftJoin('users', 'analytics_alseco_data.account_number', '=', 'users.personal_account')
            ->leftJoin('residential_complexes', 'users.residential_complex_id', '=', 'residential_complexes.id')
            ->whereNotNull('analytics_alseco_data.year')
            ->whereNotNull('analytics_alseco_data.month')
            ->where('residential_complexes.client_id', $admin->client_id);

        if ($residentialComplexId) {
            $query->where('users.residential_complex_id', $residentialComplexId);
        }

        if ($serviceFilter !== '') {
            $query->where('analytics_alseco_data.service', $serviceFilter);
        }

        return $query;
    }

    public function servicesList(Request $request)
    {
        $query = $this->buildBaseQuery($request);

        $services = $query
            ->select('analytics_alseco_data.service')
            ->whereNotNull('analytics_alseco_data.service')
            ->distinct()
            ->orderBy('analytics_alseco_data.service')
            ->pluck('service')
            ->values();

        return response()->json(['data' => $services]);
    }

    public function accrualSummary(Request $request)
    {
        $baseQuery = $this->buildBaseQuery($request);

        $periodQuery = clone $baseQuery;
        $maxYm = $periodQuery
            ->selectRaw('MAX(analytics_alseco_data.year * 100 + analytics_alseco_data.month) as max_ym')
            ->value('max_ym');

        if ($maxYm) {
            $year  = intdiv($maxYm, 100);
            $month = $maxYm % 100;

            $baseQuery->where('analytics_alseco_data.year', $year)
                ->where('analytics_alseco_data.month', $month);
        }

        $twoMonthsAgo     = now()->subMonthsNoOverflow(2)->startOfDay()->toDateString();
        $overdueCondition = "
            (
                analytics_alseco_data.payment_date IS NOT NULL
                AND analytics_alseco_data.payment_date <> ''
                AND to_date(analytics_alseco_data.payment_date, 'DD.MM.YYYY') < '{$twoMonthsAgo}'
            )
            OR ABS(analytics_alseco_data.balance_end) > 45000
        ";

        $totals = $baseQuery
            ->selectRaw("
                SUM(analytics_alseco_data.initial_accrual) as initial_accrual_sum,
                SUM(analytics_alseco_data.accrual_change) as accrual_change_sum,
                SUM(analytics_alseco_data.initial_accrual + analytics_alseco_data.accrual_change) as accrual_total_sum,
                SUM(COALESCE(analytics_alseco_data.payment, 0)) as payment_sum,
                SUM(
                    (analytics_alseco_data.initial_accrual + analytics_alseco_data.accrual_change)
                    - COALESCE(analytics_alseco_data.payment, 0)
                ) as diff_sum,
                SUM(
                    CASE WHEN {$overdueCondition}
                        THEN analytics_alseco_data.initial_accrual + analytics_alseco_data.accrual_change
                        ELSE 0
                    END
                ) as overdue_accrual_sum,
                COUNT(*) as rows_count,
                SUM(CASE WHEN {$overdueCondition} THEN 1 ELSE 0 END) as overdue_count
            ")
            ->first();

        return response()->json([
            'data' => [
                'initial_accrual_sum' => (float)($totals->initial_accrual_sum ?? 0),
                'accrual_change_sum'  => (float)($totals->accrual_change_sum ?? 0),
                'accrual_total_sum'   => (float)($totals->accrual_total_sum ?? 0),
                'payment_sum'         => (float)($totals->payment_sum ?? 0),
                'diff_sum'            => (float)($totals->diff_sum ?? 0),
                'overdue_accrual_sum' => (float)($totals->overdue_accrual_sum ?? 0),
                'rows_count'          => (int)($totals->rows_count ?? 0),
                'overdue_count'       => (int)($totals->overdue_count ?? 0),
            ]
        ]);
    }

    public function balanceSummary(Request $request)
    {
        $baseQuery = $this->buildBaseQuery($request);

        $periodQuery = clone $baseQuery;
        $maxYm = $periodQuery
            ->selectRaw('MAX(analytics_alseco_data.year * 100 + analytics_alseco_data.month) as max_ym')
            ->value('max_ym');

        if ($maxYm) {
            $year  = intdiv($maxYm, 100);
            $month = $maxYm % 100;

            $baseQuery->where('analytics_alseco_data.year', $year)
                ->where('analytics_alseco_data.month', $month);
        }

        $twoMonthsAgo     = now()->subMonthsNoOverflow(2)->startOfDay()->toDateString();
        $overdueCondition = "
            (
                analytics_alseco_data.payment_date IS NOT NULL
                AND analytics_alseco_data.payment_date <> ''
                AND to_date(analytics_alseco_data.payment_date, 'DD.MM.YYYY') < '{$twoMonthsAgo}'
            )
            OR ABS(analytics_alseco_data.balance_end) > 45000
        ";

        $totals = $baseQuery
            ->selectRaw("
                SUM(analytics_alseco_data.balance_end) as balance_total_sum,
                SUM(
                    CASE WHEN {$overdueCondition}
                        THEN analytics_alseco_data.balance_end
                        ELSE 0
                    END
                ) as overdue_balance_sum,
                COUNT(*) as rows_count,
                SUM(CASE WHEN {$overdueCondition} THEN 1 ELSE 0 END) as overdue_count
            ")
            ->first();

        return response()->json([
            'data' => [
                'balance_total_sum'   => (float)($totals->balance_total_sum ?? 0),
                'overdue_balance_sum' => (float)($totals->overdue_balance_sum ?? 0),
                'rows_count'          => (int)($totals->rows_count ?? 0),
                'overdue_count'       => (int)($totals->overdue_count ?? 0),
            ]
        ]);
    }

    public function periodsMap(Request $request)
    {
        $baseQuery = $this->buildBaseQuery($request);

        $rows = $baseQuery
            ->selectRaw('
                residential_complexes.id   as residential_complex_id,
                residential_complexes.name as residential_complex_name,
                analytics_alseco_data.year,
                analytics_alseco_data.month,
                COUNT(*) as rows_count
            ')
            ->groupBy(
                'residential_complexes.id',
                'residential_complexes.name',
                'analytics_alseco_data.year',
                'analytics_alseco_data.month'
            )
            ->orderBy('residential_complexes.name')
            ->orderBy('analytics_alseco_data.year')
            ->orderBy('analytics_alseco_data.month')
            ->get();

        return response()->json(['data' => $rows]);
    }

    public function accrualDynamics(Request $request)
    {
        $baseQuery = $this->buildBaseQuery($request);

        $rows = $baseQuery
            ->selectRaw("
                analytics_alseco_data.year,
                analytics_alseco_data.month,
                SUM(analytics_alseco_data.initial_accrual + analytics_alseco_data.accrual_change) as accrual_total_sum,
                SUM(COALESCE(analytics_alseco_data.payment, 0)) as payment_sum,
                SUM(
                    (analytics_alseco_data.initial_accrual + analytics_alseco_data.accrual_change)
                    - COALESCE(analytics_alseco_data.payment, 0)
                ) as diff_sum,
                COUNT(*) as rows_count
            ")
            ->groupBy('analytics_alseco_data.year', 'analytics_alseco_data.month')
            ->orderBy('analytics_alseco_data.year')
            ->orderBy('analytics_alseco_data.month')
            ->get();

        return response()->json(['data' => $rows]);
    }
}