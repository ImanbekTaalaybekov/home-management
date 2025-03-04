<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use App\Models\InputDebtDataAlseco;
use App\Models\InputDebtDataIvc;
use App\Models\User;

class DebtImportController extends Controller
{
    public function importDebt()
    {
        Debt::truncate();

        $alsecoDebts = InputDebtDataAlseco::all();

        foreach ($alsecoDebts as $alsecoDebt) {
            $user = User::where('personal_account', $alsecoDebt->account_number)->first();

            if ($user) {
                Debt::updateOrCreate([
                    'user_id' => $user->id,
                    'type' => 'Alseco',
                    'name' => $alsecoDebt->service,
                    'amount' => $alsecoDebt->debt_amount,
                    'due_date' => $alsecoDebt->last_payment_date,
                ]);
            }
        }

        $ivcDebts = InputDebtDataIvc::all();

        foreach ($ivcDebts as $ivcDebt) {
            $user = User::where('personal_account', $ivcDebt->account_number)->first();

            if ($user) {
                Debt::updateOrCreate([
                    'user_id' => $user->id,
                    'type' => 'IVC',
                    'name' => $ivcDebt->service_name,
                    'amount' => $ivcDebt->debt + $ivcDebt->penalty,
                    'due_date' => now()->format('Y-m-d')
                ]);
            }
        }
    }
}
