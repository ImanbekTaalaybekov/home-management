<?php

namespace App\Http\Controllers;

use App\Http\Resources\DebtResource;
use App\Models\Debt;
use Illuminate\Support\Facades\Auth;

class DebtController extends Controller
{
    public function getUserDebts()
    {
        $user = Auth::user();
        $allNames = Debt::distinct()->pluck('name');
        $userDebts = Debt::where('user_id', $user->id)->get();

        $grouped = $allNames->map(function ($name) use ($userDebts) {
            $debtsByName = $userDebts->where('name', $name);

            if ($debtsByName->isEmpty()) {
                return [
                    'name' => $name,
                    'amount' => 0,
                    'current_charges' => 0,
                    'due_date' => null,
                    'type' => null,
                ];
            }

            return [
                'name' => $name,
                'amount' => $debtsByName->sum('amount'),
                'current_charges' => $debtsByName->sum('current_charges'),
                'due_date' => $debtsByName->first()->due_date,
                'type' => $debtsByName->first()->type,
            ];
        });

        return DebtResource::collection($grouped);
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
}
