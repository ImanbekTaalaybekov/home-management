<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Debt;
use App\Services\PaymentService;

class DebtController extends Controller
{
    public function index(Request $request)
    {
        $debts = Debt::where('user_id', $request->user()->id)
            ->orderBy('due_date', 'asc')
            ->get();

        return response()->json($debts);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|string',
            'name' => 'nullable|string',
            'amount' => 'required|numeric',
            'due_date' => 'required|date',
        ]);

        $debt = Debt::create($request->all());

        return response()->json($debt, 201);
    }

    public function upload(Request $request, PaymentService $paymentService)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('file');
        $filePath = $file->store('temp');
        $paymentService->processPayments(storage_path('app/' . $filePath));

        return response()->json(['message' => 'Данные успешно загружены']);
    }
}
