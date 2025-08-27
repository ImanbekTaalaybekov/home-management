<?php

namespace App\Http\Controllers;

use App\Models\CompanyReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CompanyReportController extends Controller
{
    public function store(Request $request)
    {
        $documentPath = null;
        if ($request->hasFile('document')) {
            $documentPath = $request->file('document')
                ->store('documents/company-report', 'public');
        }

        $report = CompanyReport::create([
            'title'                  => $request->title,
            'message'                => $request->message,
            'document'               => $documentPath,
            'residential_complex_id' => $request->residential_complex_id,
        ]);

        return response()->json([
            'message' => 'Отчёт сохранён',
            'report'  => $report,
        ], 201);
    }

    public function index(Request $request)
    {
        $user = Auth::guard('sanctum')->user();

        $perPage = (int) $request->query('per_page', 10);
        $perPage = max(1, min($perPage, 100));

        $reports = CompanyReport::query()
            ->whereNull('residential_complex_id')
            ->orWhere('residential_complex_id', $user->residential_complex_id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

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

    public function remove($id)
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

        DB::transaction(function () use ($report) {
            if ($report->document && Storage::disk('public')->exists($report->document)) {
                Storage::disk('public')->delete($report->document);
            }
            $report->delete();
        });

        return response()->json(['message' => 'Отчёт и документ удалены']);
    }
}
