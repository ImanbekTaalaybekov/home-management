<?php

namespace App\Http\Controllers;

use App\Models\CompanyReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CompanyReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::guard('sanctum')->user();

        $reports = CompanyReport::query()
            ->whereNull('residential_complex_id')
            ->orWhere('residential_complex_id', $user->residential_complex_id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $reports->getCollection()->transform(function ($r) {
            $r->document_url = $r->document ? asset('storage/' . $r->document) : null;
            return $r;
        });

        return response()->json($reports);
    }

    public function show($id)
    {
        $user = Auth::guard('sanctum')->user();

        $report = CompanyReport::where('id', $id)
            ->where(function ($q) use ($user) {
                $q->whereNull('residential_complex_id')
                    ->orWhere('residential_complex_id', $user->residential_complex_id);
            })
            ->first();

        if (!$report) {
            return response()->json(['message' => 'Отчёт не найден'], 404);
        }

        $report->document_url = $report->document ? asset('storage/' . $report->document) : null;

        return response()->json($report);
    }
}
