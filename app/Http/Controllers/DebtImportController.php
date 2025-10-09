<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use App\Models\DebtPaymentCheck;
use App\Models\InputDebtDataAlseco;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DebtImportController extends Controller
{
    public function importDebt()
    {
        Debt::truncate();
        DebtPaymentCheck::truncate();

        $services = InputDebtDataAlseco::query()
            ->whereNotNull('service')
            ->distinct()
            ->pluck('service')
            ->values();

        if ($services->isEmpty()) {
            return response()->json(['message' => 'Нет данных Alseco для импорта'], 200);
        }

        $accounts = InputDebtDataAlseco::query()
            ->distinct()
            ->pluck('account_number')
            ->filter()
            ->values();

        foreach ($accounts->chunk(500) as $accountChunk) {

            $usersByAccount = User::query()
                ->whereIn('personal_account', $accountChunk)
                ->get()
                ->keyBy('personal_account');

            $agg = InputDebtDataAlseco::query()
                ->whereIn('account_number', $accountChunk)
                ->whereNotNull('service')
                ->select([
                    'account_number',
                    'service',
                    DB::raw('SUM(ABS(debt_amount)) AS amount'),
                    DB::raw('SUM(current_charges) AS current_charges'),
                    DB::raw('MAX(last_payment_date) AS due_date'),
                ])
                ->groupBy('account_number', 'service')
                ->get()
                ->groupBy('account_number');

            foreach ($accountChunk as $account) {
                $user = $usersByAccount->get($account);
                if (!$user) {
                    continue;
                }

                $rowsByService = ($agg->get($account) ?? collect())->keyBy('service');

                foreach ($services as $serviceName) {
                    $row = $rowsByService->get($serviceName);

                    $amount          = $row ? (float)$row->amount : 0.0;
                    $currentCharges  = $row ? (float)$row->current_charges : 0.0;
                    $dueDate         = $row ? $row->due_date : null;

                    Debt::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'type'    => 'Alseco',
                            'name'    => $serviceName,
                        ],
                        [
                            'amount'           => $amount,
                            'current_charges'  => $currentCharges,
                            'due_date'         => $dueDate,
                        ]
                    );
                }
            }
        }

        /*$ivcDebts = InputDebtDataIvc::all();

        foreach ($ivcDebts as $ivcDebt) {
            $user = User::where('personal_account', $ivcDebt->account_number)->first();

            if ($user) {
                Debt::updateOrCreate([
                    'user_id' => $user->id,
                    'type' => 'IVC',
                    'name' => $ivcDebt->service_name,
                    'amount' => abs($ivcDebt->debt + $ivcDebt->penalty),
                    'due_date' => now()->format('Y-m-d')
                ]);
            }
        }*/

        return response()->json(['message' => 'Данные импортированы']);
    }
}
