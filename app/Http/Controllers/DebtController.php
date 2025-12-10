<?php

namespace App\Http\Controllers;

use App\Http\Resources\DebtResource;
use App\Models\Debt;
use App\Models\DebtPaymentCheck;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DebtController extends Controller
{
    public function getUserDebts()
    {
        $user = Auth::user();
        $lang = strtolower($user->language ?? 'ru');
        $allowed = ['ru','kg','uz','kk','en','es','zh'];
        if (!in_array($lang, $allowed, true)) {
            $lang = 'ru';
        }

        $debts = Debt::where('user_id', $user->id)
            ->with('translation')
            ->get()
            ->map(function ($debt) use ($lang) {
                $t = $debt->translation;
                if ($t && !empty($t->{$lang})) {
                    $debt->name = $t->{$lang};
                } elseif ($t && !empty($t->ru)) {
                    $debt->name = $t->ru;
                }
                return $debt;
            });

        return DebtResource::collection($debts);
    }

    public function getSingleDebt($id)
    {
        $debt = Debt::with('translation')->findOrFail($id);
        $user = User::find($debt->user_id);
        $lang = strtolower($user->language ?? 'ru');
        $allowed = ['ru','kg','uz','kk','en','es','zh'];
        if (!in_array($lang, $allowed, true)) {
            $lang = 'ru';
        }

        $t = $debt->translation;
        if ($t && !empty($t->{$lang})) {
            $debt->name = $t->{$lang};
        } elseif ($t && !empty($t->ru)) {
            $debt->name = $t->ru;
        }

        return new DebtResource($debt);
    }

    public function getUserDebtsSum()
    {
        $user = Auth::user();
        $total = Debt::where('user_id', $user->id)->sum('amount');
        $total = ABS($total);

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