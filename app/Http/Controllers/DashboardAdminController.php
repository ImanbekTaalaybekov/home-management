<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\User;
use App\Models\ResidentialComplex;
use App\Models\Poll;
use App\Models\Complaint;
use App\Models\ServiceRequest;
use App\Models\Suggestion;
use App\Models\AnalyticsAlsecoData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardAdminController extends Controller
{
    public function index(Request $request)
    {
        /** @var Admin $admin */
        $admin = Auth::guard('sanctum')->user();

        if (!$admin || !$admin->client_id) {
            return response()->json([
                'message' => 'У администратора не указан client_id',
            ], 400);
        }

        $clientId = $admin->client_id;

        $filterResidentialId = $request->query('residential_complex_id');

        $resComplexQuery = ResidentialComplex::where('client_id', $clientId);

        if (!empty($filterResidentialId)) {
            $resComplexQuery->where('id', $filterResidentialId);
        }

        $resComplexIds = $resComplexQuery->pluck('id');

        if ($resComplexIds->isEmpty()) {
            return response()->json([
                'polls' => [
                    'total'    => 0,
                    'finished' => 0,
                ],
                'residents' => [
                    'total'        => 0,
                    'blocks_total' => 0,
                ],
                'residential_complexes_total' => 0,
                'complaints' => [
                    'total'    => 0,
                    'done'     => 0,
                ],
                'suggestions' => [
                    'total'    => 0,
                    'done'     => 0,
                ],
                'service_requests' => [
                    'total'    => 0,
                    'done'     => 0,
                ],
                'analytics_last_period' => [
                    'year'  => null,
                    'month' => null,
                ],
            ]);
        }

        $userIds = User::whereIn('residential_complex_id', $resComplexIds)->pluck('id');

        $now = now();

        $pollsTotal = Poll::whereIn('residential_complex_id', $resComplexIds)->count();

        $pollsFinished = Poll::whereIn('residential_complex_id', $resComplexIds)
            ->where('end_date', '<', $now)
            ->count();

        $usersCount = User::whereIn('residential_complex_id', $resComplexIds)->count();

        $blocksCount = User::whereIn('residential_complex_id', $resComplexIds)
            ->whereNotNull('block_number')
            ->where('block_number', '!=', '')
            ->distinct('block_number')
            ->count('block_number');

        $resComplexCount = $resComplexIds->count();
        $complaintsTotal = Complaint::whereIn('user_id', $userIds)->count();
        $complaintsDone = Complaint::whereIn('user_id', $userIds)
            ->where('status', 'done')
            ->count();

        $suggestionsTotal = Suggestion::whereIn('user_id', $userIds)->count();

        $suggestionsDone = Suggestion::whereIn('user_id', $userIds)
            ->where('status', 'done')
            ->count();

        $serviceRequestsTotal = ServiceRequest::whereIn('user_id', $userIds)->count();
        $serviceRequestsDone = ServiceRequest::whereIn('user_id', $userIds)
            ->where('status', 'done')
            ->count();

        $lastYear = AnalyticsAlsecoData::max('year');
        $lastMonth = null;

        if ($lastYear) {
            $lastMonth = AnalyticsAlsecoData::where('year', $lastYear)->max('month');
        }

        return response()->json([
            'polls' => [
                'total'    => $pollsTotal,
                'finished' => $pollsFinished,
            ],

            'residents' => [
                'total'        => $usersCount,
                'blocks_total' => $blocksCount,
            ],

            'residential_complexes_total' => $resComplexCount,

            'complaints' => [
                'total'    => $complaintsTotal,
                'done'     => $complaintsDone,
            ],

            'suggestions' => [
                'total'    => $suggestionsTotal,
                'done'     => $suggestionsDone,
            ],

            'service_requests' => [
                'total'    => $serviceRequestsTotal,
                'done'     => $serviceRequestsDone,
            ],

            'analytics_last_period' => [
                'year'  => $lastYear,
                'month' => $lastMonth,
            ],
        ]);
    }
}