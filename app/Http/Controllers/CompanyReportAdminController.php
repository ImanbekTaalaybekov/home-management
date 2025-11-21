<?php

namespace App\Http\Controllers;

use App\Models\CompanyReport;
use App\Models\ResidentialComplex;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CompanyReportAdminController extends Controller
{
    public function store(Request $request)
    {
        $admin = Auth::guard('sanctum')->user();
        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'title'                  => 'required|string|max:255',
            'message'                => 'required|string',
            'residential_complex_id' => 'nullable|exists:residential_complexes,id',
            'document'               => 'nullable|file|mimes:pdf',
        ]);

        if ($request->filled('residential_complex_id')) {
            $complex = ResidentialComplex::where('id', $request->residential_complex_id)
                ->where('client_id', $admin->client_id)
                ->first();

            if (!$complex) {
                return response()->json(['message' => 'Forbidden: residential complex not belongs to this client'], 403);
            }
        }

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

        $report->document_url = $report->document ? asset('storage/' . $report->document) : null;

        return response()->json([
            'message' => 'Отчёт сохранён',
            'report'  => $report,
        ], 201);
    }

    public function index(Request $request)
    {
        $admin = Auth::guard('sanctum')->user();
        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'residential_complex_id' => 'nullable|exists:residential_complexes,id',
        ]);

        $query = CompanyReport::query()
            ->with('residentialComplex')
            ->where(function ($q) use ($admin) {
                $q->whereNull('residential_complex_id')
                    ->orWhereHas('residentialComplex', function ($qq) use ($admin) {
                        $qq->where('client_id', $admin->client_id);
                    });
            });

        if ($request->filled('residential_complex_id')) {
            $query->where('residential_complex_id', $request->residential_complex_id);
        }

        $reports = $query
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $reports->getCollection()->transform(function ($r) {
            $r->document_url = $r->document ? asset('storage/' . $r->document) : null;
            return $r;
        });

        return response()->json($reports);
    }

    public function update(Request $request, $id)
    {
        $admin = Auth::guard('sanctum')->user();
        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'title'                  => 'nullable|string|max:255',
            'message'                => 'nullable|string',
            'residential_complex_id' => 'nullable|exists:residential_complexes,id',
            'document'               => 'nullable|file|mimes:pdf',
        ]);

        $report = CompanyReport::where('id', $id)
            ->where(function ($q) use ($admin) {
                $q->whereNull('residential_complex_id')
                    ->orWhereHas('residentialComplex', function ($qq) use ($admin) {
                        $qq->where('client_id', $admin->client_id);
                    });
            })
            ->first();

        if (!$report) {
            return response()->json(['message' => 'Отчёт не найден'], 404);
        }

        $data = [];

        if ($request->filled('title')) {
            $data['title'] = $request->title;
        }
        if ($request->filled('message')) {
            $data['message'] = $request->message;
        }

        if ($request->has('residential_complex_id')) {
            if ($request->residential_complex_id) {
                $complex = ResidentialComplex::where('id', $request->residential_complex_id)
                    ->where('client_id', $admin->client_id)
                    ->first();

                if (!$complex) {
                    return response()->json(['message' => 'Forbidden: residential complex not belongs to this client'], 403);
                }

                $data['residential_complex_id'] = $request->residential_complex_id;
            } else {
                $data['residential_complex_id'] = null;
            }
        }

        if ($request->hasFile('document')) {
            if ($report->document && Storage::disk('public')->exists($report->document)) {
                Storage::disk('public')->delete($report->document);
            }

            $data['document'] = $request->file('document')
                ->store('documents/company-report', 'public');
        }

        if (!empty($data)) {
            $report->update($data);
        }

        $report->document_url = $report->document ? asset('storage/' . $report->document) : null;

        return response()->json([
            'message' => 'Отчёт обновлён',
            'report'  => $report,
        ]);
    }

    public function remove($id)
    {
        $admin = Auth::guard('sanctum')->user();
        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $report = CompanyReport::where('id', $id)
            ->where(function ($q) use ($admin) {
                $q->whereNull('residential_complex_id')
                    ->orWhereHas('residentialComplex', function ($qq) use ($admin) {
                        $qq->where('client_id', $admin->client_id);
                    });
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
