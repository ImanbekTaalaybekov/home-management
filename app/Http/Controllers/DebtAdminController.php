<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsAlsecoData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DebtAdminController extends Controller
{
    public function adminServiceList(Request $request)
    {
        $admin = Auth::guard('sanctum')->user();

        if (!$admin) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if (!$admin->client_id) {
            return response()->json(['message' => 'У админа не указан client_id'], 403);
        }

        $allParam = $request->query('all', 'false');
        $all = filter_var($allParam, FILTER_VALIDATE_BOOLEAN);
        $residentialComplexId = $request->query('residential_complex_id');
        $search = trim((string)$request->query('search', ''));

        $baseQuery = AnalyticsAlsecoData::query()
            ->leftJoin('users', 'analytics_alseco_data.account_number', '=', 'users.personal_account')
            ->leftJoin('residential_complexes', 'users.residential_complex_id', '=', 'residential_complexes.id')
            ->whereNotNull('analytics_alseco_data.year')
            ->whereNotNull('analytics_alseco_data.month')
            ->where('residential_complexes.client_id', $admin->client_id);

        if ($residentialComplexId) {
            $baseQuery->where('users.residential_complex_id', $residentialComplexId);
        }

        if ($search !== '') {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('analytics_alseco_data.account_number', 'like', "%{$search}%")
                    ->orWhere('analytics_alseco_data.full_name', 'like', "%{$search}%")
                    ->orWhere('users.name', 'like', "%{$search}%")
                    ->orWhere('analytics_alseco_data.service', 'like', "%{$search}%");
            });
        }

        if (!$all) {
            $periodQuery = clone $baseQuery;
            $maxYm = $periodQuery
                ->selectRaw('MAX(analytics_alseco_data.year * 100 + analytics_alseco_data.month) as max_ym')
                ->value('max_ym');

            if ($maxYm) {
                $year  = intdiv($maxYm, 100);
                $month = $maxYm % 100;

                $baseQuery->where('analytics_alseco_data.year', $year)
                    ->where('analytics_alseco_data.month', $month);
            }
        }

        $rows = $baseQuery
            ->select([
                'analytics_alseco_data.*',
                'users.name as resident_name',
                'users.residential_complex_id',
                'residential_complexes.name as residential_complex_name',
            ])
            ->orderBy('analytics_alseco_data.year', 'desc')
            ->orderBy('analytics_alseco_data.month', 'desc')
            ->orderBy('analytics_alseco_data.account_number')
            ->paginate(50);

        return response()->json($rows);
    }
}