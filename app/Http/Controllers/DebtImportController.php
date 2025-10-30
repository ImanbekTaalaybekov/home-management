<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsAlsecoData;
use App\Models\Debt;
use App\Models\DebtPaymentCheck;
use App\Models\User;

class DebtImportController extends Controller
{
    public function importDebt()
    {
        set_time_limit(3600);

        Debt::truncate();
        DebtPaymentCheck::truncate();

        $lastKey = AnalyticsAlsecoData::query()
            ->whereNotNull('year')
            ->whereNotNull('month')
            ->selectRaw('MAX(year * 100 + month) as k')
            ->value('k');

        if (!$lastKey) {
            return response()->json(['message' => 'Нет данных AnalyticsAlsecoData для импорта'], 200);
        }

        $lastYear  = intdiv((int)$lastKey, 100);
        $lastMonth = (int)$lastKey % 100;

        $accounts = AnalyticsAlsecoData::query()
            ->where('year',  $lastYear)
            ->where('month', $lastMonth)
            ->whereNotNull('account_number')
            ->distinct()
            ->pluck('account_number')
            ->filter()
            ->values();

        if ($accounts->isEmpty()) {
            return response()->json(['message' => 'Нет лицевых счетов за последний период'], 200);
        }

        $totalWritten = 0;

        foreach ($accounts->chunk(500) as $accountChunk) {

            $usersByAccount = User::query()
                ->whereIn('personal_account', $accountChunk)
                ->get()
                ->keyBy('personal_account');

            $agg = AnalyticsAlsecoData::query()
                ->whereIn('account_number', $accountChunk)
                ->where('year',  $lastYear)
                ->where('month', $lastMonth)
                ->whereNotNull('service')
                ->select([
                    'account_number',
                    'service',
                ])
                ->selectRaw('SUM(ABS(COALESCE(balance_start, 0)))   AS period_start_balance')
                ->selectRaw('SUM(ABS(COALESCE(balance_end, 0)))     AS amount')
                ->selectRaw('SUM(ABS(COALESCE(accrual_change, 0)))  AS current_charges')
                ->selectRaw('SUM(ABS(COALESCE(payment, 0)))         AS payment_amount')
                ->selectRaw('SUM(ABS(COALESCE(initial_accrual, 0))) AS initial_amount')
                ->selectRaw("MAX(NULLIF(payment_date, ''))          AS due_date")
                ->groupBy('account_number', 'service')
                ->get()
                ->groupBy('account_number');

            foreach ($accountChunk as $account) {
                $user = $usersByAccount->get($account);
                if (!$user) continue;

                $rowsByService = ($agg->get($account) ?? collect())->keyBy('service');

                foreach ($rowsByService as $serviceName => $row) {
                    Debt::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'type'    => 'Alseco',
                            'name'    => $serviceName,
                        ],
                        [
                            'period_start_balance' => (float)$row->period_start_balance,
                            'amount'               => (float)$row->amount,
                            'current_charges'      => (float)$row->current_charges,
                            'payment_amount'       => (float)$row->payment_amount,
                            'initial_amount'       => (float)$row->initial_amount,
                            'due_date'             => $row->due_date ?: null,
                        ]
                    );
                    $totalWritten++;
                }
            }
        }

        return response()->json([
            'message'     => 'Данные импортированы из AnalyticsAlsecoData',
            'period_used' => ['year' => $lastYear, 'month' => $lastMonth],
            'rows'        => $totalWritten,
        ]);
    }
}
