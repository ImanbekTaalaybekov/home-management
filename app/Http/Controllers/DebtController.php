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
}
