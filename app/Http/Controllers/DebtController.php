<?php

namespace App\Http\Controllers;

use App\Http\Resources\DebtResource;
use App\Models\Debt;
use App\Models\DebtPaymentCheck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DebtController extends Controller
{
    public function getUserDebts()
    {
        $user = Auth::user();

        $debts = Debt::where('user_id', $user->id)->get();

        return DebtResource::collection($debts);
    }

    public function getSingleDebt($id)
    {
        $debt = Debt::findOrFail($id);
        return new DebtResource($debt);
    }

    public function getUserDebtsSum()
    {
        $user = Auth::user();
        $total = Debt::where('user_id', $user->id)->sum('amount');

        return response()->json(['total_debt' => $total]);
    }

    public function getCheckedDebts()
    {
        $user = Auth::user();

        $checked = DebtPaymentCheck::where('user_id', $user->id)
            ->pluck('debt_id')
            ->toArray();

        return response()->json(['checked_debts' => $checked]);
    }

    public function toggleDebtCheck(Request $request, $debtId)
    {
        $user = Auth::user();

        $check = DebtPaymentCheck::where('user_id', $user->id)
            ->where('debt_id', $debtId)
            ->first();

        if ($check) {
            $check->delete();
            $status = false;
        } else {
            DebtPaymentCheck::create([
                'user_id' => $user->id,
                'debt_id' => $debtId,
            ]);
            $status = true;
        }

        return response()->json([
            'debt_id' => $debtId,
            'checked' => $status,
        ]);
    }

    public function isDebtChecked($debtId)
    {
        $user = Auth::user();

        $exists = DebtPaymentCheck::where('user_id', $user->id)
            ->where('debt_id', $debtId)
            ->exists();

        return response()->json([
            'debt_id' => $debtId,
            'checked' => $exists,
        ]);
    }
}